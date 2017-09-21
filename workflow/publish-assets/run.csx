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
using System.Text.RegularExpressions;

private static CloudMediaContext _context = null;
private static readonly string _amsAADTenantDomain = Environment.GetEnvironmentVariable("AMSAADTenantDomain");
private static readonly string _amsRestApiEndpoint = Environment.GetEnvironmentVariable("AMSRestApiEndpoint");
private static readonly string _amsClientId = Environment.GetEnvironmentVariable("AMSClientId");
private static readonly string _amsClientSecret = Environment.GetEnvironmentVariable("AMSClientSecret");
private static readonly string _amsStorageAccountName = Environment.GetEnvironmentVariable("AMSStorageAccountName");
private static readonly string _amsStorageAccountKey = Environment.GetEnvironmentVariable("AMSStorageAccountKey");

class Meta
{
  public string id;
  public string name;
  public string asset_name;
  public string thumbnail_url;
  public string manifest_url;
  public string lang;
  public string last_updated_time;
  public string description;
  public string webvtt_url;
  public object subtitle_urls;
}

public static async Task<object> Run(HttpRequestMessage req, IAsyncCollector<object> outputDocument, IAsyncCollector<string> outputQueueItem, TraceWriter log)
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

        Meta meta = new Meta();
        List<object> subtitleUrlList = new List<object>();

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
                meta.id = assetid;
                meta.manifest_url = streamingUrl;
                meta.name = asset.Name;
                meta.asset_name = asset.Name;
                log.Info("Streaming URL : " + streamingUrl);
            }

            // Get thumbnail_url
            var thumbnailFile = asset.AssetFiles.Where(f => f.Name.ToLower().EndsWith(".png")).FirstOrDefault();
            if (thumbnailFile != null && outputLocator != null) {
                meta.thumbnail_url = outputLocator.Path + thumbnailFile.Name;
                log.Info("thumbnail url : " + meta.thumbnail_url);                     
            }

            // Get caption and subtitle webvtts in the output asset
            IEnumerable<IAssetFile> webvtts = asset
                    .AssetFiles
                    .ToList()
                    .Where(af => af.Name.EndsWith(".vtt", StringComparison.OrdinalIgnoreCase));

            foreach(IAssetFile af in webvtts)
            {
                var filename = af.Name;
                if (filename.EndsWith("_aud_SpReco.vtt", StringComparison.OrdinalIgnoreCase) ) {
                    meta.webvtt_url = outputLocator.Path + filename;
                    log.Info("webvtt url : " + meta.webvtt_url);                     
                }
                if (filename.StartsWith("subtitle", StringComparison.OrdinalIgnoreCase) ) {
                    
                    Match matched = Regex.Match(filename, @"subtitle-(.*)\.vtt");
                    if (matched.Success)
                    {
                        var matched_lang = matched.Groups[1].Value;
                        subtitleUrlList.Add(
                            new {
                                lang = matched_lang, 
                                webvtt_url = outputLocator.Path + filename
                            }
                        );
                    }
                }
            }
        }

        object outdoc = new {
            id = meta.id,
            name = meta.name,
            asset_name = meta.asset_name,
            thumbnail_url=meta.thumbnail_url,
            lang = "en",
            description = "dummy description",
            manifest_url = meta.manifest_url,
            webvtt_url = meta.webvtt_url,
            subtitle_urls = subtitleUrlList.ToArray(),
        };
        await outputDocument.AddAsync(outdoc);
 
        string outqueue = meta.id;
        await outputQueueItem.AddAsync(outqueue);
 
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

