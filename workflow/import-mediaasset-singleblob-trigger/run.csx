#r "Newtonsoft.Json"
#r "Microsoft.WindowsAzure.Storage"

using System;
using System.Net;
using System.Text;
using Newtonsoft.Json;
using Microsoft.WindowsAzure.MediaServices.Client;
using System.Threading.Tasks;
using Microsoft.WindowsAzure.Storage;
using Microsoft.WindowsAzure.Storage.Blob;
using Microsoft.WindowsAzure.Storage.Auth;
using Microsoft.IdentityModel.Clients.ActiveDirectory;

private static CloudMediaContext _context = null;
private static readonly string _amsAADTenantDomain = Environment.GetEnvironmentVariable("AMSAADTenantDomain");
private static readonly string _amsRestApiEndpoint = Environment.GetEnvironmentVariable("AMSRestApiEndpoint");
private static readonly string _amsClientId = Environment.GetEnvironmentVariable("AMSClientId");
private static readonly string _amsClientSecret = Environment.GetEnvironmentVariable("AMSClientSecret");
private static readonly string _amsStorageAccountName = Environment.GetEnvironmentVariable("AMSStorageAccountName");
private static readonly string _amsStorageAccountKey = Environment.GetEnvironmentVariable("AMSStorageAccountKey");

public static async Task<object> Run(CloudBlockBlob myBlob, string name, TraceWriter log)
{
    log.Info($"C# Blob trigger function Processed blob\n Name:{name} \n URI:{myBlob.StorageUri}");

    IAsset newAsset = null;
    IIngestManifest manifest = null;
    try
    {
        // Load AMS account context
        log.Info($"Using Azure Media Service Rest API Endpoint : {_amsRestApiEndpoint}");
        AzureAdTokenCredentials tokenCredentials = new AzureAdTokenCredentials(_amsAADTenantDomain,
            new AzureAdClientSymmetricKey(_amsClientId, _amsClientSecret),
            AzureEnvironments.AzureCloudEnvironment);
        AzureAdTokenProvider tokenProvider = new AzureAdTokenProvider(tokenCredentials);
        _context = new CloudMediaContext(new Uri(_amsRestApiEndpoint), tokenProvider);

        //log.Info("Using Azure Media Services account : " + _mediaServicesAccountName);
        //_context = new CloudMediaContext(new MediaServicesCredentials(_mediaServicesAccountName, _mediaServicesAccountKey));

        // Create Asset
        newAsset = _context.Assets.Create(name, AssetCreationOptions.None);
        log.Info("Created Azure Media Services Asset : ");
        log.Info("  - Asset Name = " + name);
        log.Info("  - Asset Creation Option = " + AssetCreationOptions.None);

        // Setup blob container
        //CloudBlobContainer sourceBlobContainer = GetCloudBlobContainer(_sourceStorageAccountName, _sourceStorageAccountKey, config.IngestSource.SourceContainerName);
        CloudBlobContainer destinationBlobContainer = GetCloudBlobContainer(_amsStorageAccountName, _amsStorageAccountKey, newAsset.Uri.Segments[1]);
        //sourceBlobContainer.CreateIfNotExists();
        // Copy Source Blob container into Destination Blob container that is associated with the asset.
        CopyBlobsAsync(myBlob, destinationBlobContainer, log);
    }
    catch (Exception ex)
    {
        log.Info("Exception " + ex);
        return new HttpResponseMessage(HttpStatusCode.BadRequest);
    }

    log.Info("Asset ID : " + newAsset.Id);
    //log.Info("Source Container : " + config.IngestSource.SourceContainerName);
    
    AMSImportResponse data = new AMSImportResponse();
    data.AssetId = newAsset.Id;
    data.DestinationContainer = newAsset.Uri.Segments[1];
    var json = JsonConvert.SerializeObject(data, Formatting.Indented);
         
    return new HttpResponseMessage(HttpStatusCode.OK)
    {
        Content = new StringContent(json, Encoding.UTF8, "application/json")
    };
}

public class AMSImportResponse
{
    public string AssetId { get; set; }
    public string DestinationContainer { get; set; }
}

static public CloudBlobContainer GetCloudBlobContainer(string storageAccountName, string storageAccountKey, string containerName)
{
    CloudStorageAccount sourceStorageAccount = new CloudStorageAccount(new StorageCredentials(storageAccountName, storageAccountKey), true);
    CloudBlobClient sourceCloudBlobClient = sourceStorageAccount.CreateCloudBlobClient();
    return sourceCloudBlobClient.GetContainerReference(containerName);
}

static public void CopyBlobsAsync(CloudBlockBlob sourceBlob, CloudBlobContainer destinationBlobContainer, TraceWriter log)
{
    if (destinationBlobContainer.CreateIfNotExists())
    {
        destinationBlobContainer.SetPermissions(new BlobContainerPermissions
        {
            PublicAccess = BlobContainerPublicAccessType.Blob
        });
    }

    string blobPrefix = null;
    bool useFlatBlobListing = true;
    
    log.Info("Source blob : " + (sourceBlob as CloudBlob).Uri.ToString());
    CloudBlob destinationBlob = destinationBlobContainer.GetBlockBlobReference((sourceBlob as CloudBlob).Name);
    if (destinationBlob.Exists())
    {
         log.Info("Destination blob already exists. Skipping: " + destinationBlob.Uri.ToString());
    }
    else
    {
        log.Info("Copying blob " + sourceBlob.Uri.ToString() + " to " + destinationBlob.Uri.ToString());
        CopyBlobAsync(sourceBlob as CloudBlob, destinationBlob);
    }
}

static public async void CopyBlobAsync(CloudBlob sourceBlob, CloudBlob destinationBlob)
{
    var signature = sourceBlob.GetSharedAccessSignature(new SharedAccessBlobPolicy
    {
        Permissions = SharedAccessBlobPermissions.Read,
        SharedAccessExpiryTime = DateTime.UtcNow.AddHours(24)
    });
    await destinationBlob.StartCopyAsync(new Uri(sourceBlob.Uri.AbsoluteUri + signature));
}

