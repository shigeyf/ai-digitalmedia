#!/bin/sh

docker run --rm \
    -e CosmosdbServiceHost="https://<CosmosDBAccount>.documents.azure.com:443/" \
    -e CosmosdbMasterKey="<CosmosDBMasterKey>" \
    -e AzureSearchServiceName="<AzureSearchAccountName>" \
    -e AzureSearchApiKey="<AzureSearchAdminApiKey>" \
    -p 8080:8080 -p 2222:2222  -it ai-digitalmedia-portal:0.1.0

