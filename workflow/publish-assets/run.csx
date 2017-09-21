#r "Newtonsoft.Json"
#r "Microsoft.WindowsAzure.Storage"

using System;
using System.Net;
using Newtonsoft.Json;
using Newtonsoft.Json.Linq;
using Microsoft.WindowsAzure.MediaServices.Client;
using Microsoft.WindowsAzure.Storage;
using Microsoft.WindowsAzure.Storage.Blob;
using Microsoft.WindowsAzure.Storage.Auth;
using System.Threading.Tasks;
using Microsoft.IdentityModel.Clients.ActiveDirectory;

private static CloudMediaContext _context = null;
private static readonly string _amsAADTenantDomain = Environment.GetEnvironmentVariable("AMSAADTenantDomain");
private static readonly string _amsRestApiEndpoint = Environment.GetEnvironmentVariable("AMSRestApiEndpoint");
private static readonly string _amsClientId = Environment.GetEnvironmentVariable("AMSClientId");
private static readonly string _amsClientSecret = Environment.GetEnvironmentVariable("AMSClientSecret");
private static readonly string _amsStorageAccountName = Environment.GetEnvironmentVariable("AMSStorageAccountName");
private static readonly string _amsStorageAccountKey = Environment.GetEnvironmentVariable("AMSStorageAccountKey");

public static async Task<object> Run(HttpRequestMessage req, TraceWriter log)
{
    log.Info($"Webhook was triggered!");

    string jsonContent = await req.Content.ReadAsStringAsync();
    dynamic data = JsonConvert.DeserializeObject(jsonContent);
    log.Info("Request : " + jsonContent);
    
    // Validate input objects
    if (data.AssetIds == null)
        return req.CreateResponse(HttpStatusCode.BadRequest, new { error = "Please pass AssetIds in the input object" });
    log.Info("Input - AssetIds : " + data.AssetIds);

    string[] assetids = data.AssetIds.ToObject<string[]>();
    string streamingUrl = "";
    try
    {
        // Load AMS account context
        log.Info($"Using Azure Media Service Rest API Endpoint : {_amsRestApiEndpoint}");
        AzureAdTokenCredentials tokenCredentials = new AzureAdTokenCredentials(_amsAADTenantDomain,
            new AzureAdClientSymmetricKey(_amsClientId, _amsClientSecret),
            AzureEnvironments.AzureCloudEnvironment);
        AzureAdTokenProvider tokenProvider = new AzureAdTokenProvider(tokenCredentials);
        _context = new CloudMediaContext(new Uri(_amsRestApiEndpoint), tokenProvider);
                
        foreach (string assetid in assetids)
        {
            // Get the Asset
            var asset = _context.Assets.Where(a => a.Id == assetid).FirstOrDefault();
            if (asset == null)
            {
                log.Info("Asset not found - " + assetid);
                return req.CreateResponse(HttpStatusCode.BadRequest, new { error = "Asset not found" });
            }
            log.Info("Asset found, Asset ID : " + asset.Id);
            
            // Publish with a streaming locator
            IAccessPolicy streamingAccessPolicy = _context.AccessPolicies.Create("streamingAccessPolicy", TimeSpan.FromDays(365), AccessPermissions.Read);
            ILocator outputLocator = _context.Locators.CreateLocator(LocatorType.OnDemandOrigin, asset, streamingAccessPolicy, DateTime.UtcNow.AddMinutes(-5));
            
            var manifestFile = asset.AssetFiles.Where(f => f.Name.ToLower().EndsWith(".ism")).FirstOrDefault();
            if (manifestFile != null && outputLocator != null)
            {
                streamingUrl = outputLocator.Path + manifestFile.Name + "/manifest";
            }
            log.Info("Streaming URL : " + streamingUrl);
        }
    }
    catch (Exception ex)
    {
        log.Info("Exception " + ex);
        return req.CreateResponse(HttpStatusCode.BadRequest);
    }

    return req.CreateResponse(HttpStatusCode.OK, new
    {
        StreamingUrl = streamingUrl
    });
}
