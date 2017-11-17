---
services: media-services, functions, comsmosdb, search
platforms: dotnet, python
author: shigeyf, yoichika
---

# ai-digitalmedia

A sample demo application for AI powered digitial video library portal on Microsoft Azure cloud platform.

This sample demo application shall focus on:
 * Transforming customer’s digital media assets on-prem into Azure
 * Driving media intelligence leveraged by Video AI
 * Online Video Portal (OVP) with indexing and search capability


## What Azure Workloads are used in this sample demo application:
 - Hybrid Storage (StorSimple)
 - StorSimple Data Transformation Manager
 - Azure Media Services
 - Azure Media Analytics
 - Azure Functions
 - Azure Logic Apps
 - Azure Search
 - Azure Web App for Container
 - Cosmos DB


## How to deploy this sample demo application

### 1. [Fork this](https://github.com/shigeyf/ai-digitalmedia#fork-destination-box) to your own repo

### 2. Create an Azure Media Services account

Create a Media Services account in your subscription if don't have it already.

  <a href="https://portal.azure.com/#create/Microsoft.Template/uri/https%3A%2F%2Fraw.githubusercontent.com%2Fshigeyf%2FDeployAzureMediaServices%2Fmaster%2Fazuredeploy.json" target="_blank"><img src="http://azuredeploy.net/deploybutton.png"/></a>

  * Settings
    - **Media Services Account Name**: specify any name
    - **Storage Account Name**: specify any name
    - **Storage Option**: *Starndard_LSR* is enough for this demo

### 3. Create a Service Principal

Create a Service Principal and save the password. It will be needed in step 6.
To do so, go to the API tab in the account ([follow this article](https://docs.microsoft.com/en-us/azure/media-services/media-services-portal-get-started-with-aad#service-principal-authentication))

### 4. Make sure the AMS streaming endpoint is started

To enable streaming, go to the Azure portal, select the Azure Media Services account which has been created, and start the default streaming endpoint.

### 5. Create an Azure Search Service account

Create a Azure Search accountin your subscription.

  <a href="https://portal.azure.com/#create/Microsoft.Template/uri/https%3A%2F%2Fraw.githubusercontent.com%2Fshigeyf%2Fai-digitalmedia%2Fmaster%2Fazuredeploy-azuresearch.json" target="_blank"><img src="http://azuredeploy.net/deploybutton.png"/></a> 

  * Settings
    - **Name**: specify any name
    - **Sku**: *basic* is enough for this demo
    - **Replica Count**: *1* is enough for this demo
    - **Partition Count**: *1* is enough for this demo
    - **Hosting Mode**: *default* is enough for this demo

### 6. Create an Azure COSMOS Database account

Create a Cosmos DB accountin your subscription.
  
  <a href="https://portal.azure.com/#create/Microsoft.Template/uri/https%3A%2F%2Fraw.githubusercontent.com%2Fshigeyf%2Fai-digitalmedia%2Fmaster%2Fazuredeploy-cosmosdb.json" target="_blank"><img src="http://azuredeploy.net/deploybutton.png"/></a> 

  * Settings
    - **Database Account Name**: specify any name

### 7. Deploy sample media functions into an Azure Functions account

Create a Function account with this sample code of media functions in your subscription.

  <a href="https://portal.azure.com/#create/Microsoft.Template/uri/https%3A%2F%2Fraw.githubusercontent.com%2Fshigeyf%2Fai-digitalmedia%2Fmaster%2Fazuredeploy-functions.json" target="_blank"><img src="http://azuredeploy.net/deploybutton.png"/></a>  

  * This deployment script will not create an Azure Media Services account and an Azure Storage account 
  * Please consider Consumption Plan or App Service Plan if you will deploy manually
    * Consumption Plan – Timeout of function will be 5 mins
    * App Service Plan (Dedicated Plan) – There is no timeout (if AlwaysOn is enabled)
  * If a deployment target resource group already contains an App Service Plan (Dedicated Plan), Azure Functions app will be contained in that App Service Plan (Dedicated Plan)

  * Settings
    - **Function App Name**: specify any name
    - **Function Key**: Use as is
    - **Source Code Repository URL**: Use your github repo
    - **Source Code Branch**: Use your branch in your github repo
    - **Media Services Account Azure Active Directory Tenant Domain**: specify your own Azure AD domain for AMS account access
    - **Media Services Account Rest Api Endpoint**: specify your own AMS Api Endpoint (see Step 3)
    - **Media Services Account Service Principal Client Id**: specify your own SPN for AMS access (see Step 3)
    - **Media Services Account Service Principal Client Secret**: specify your own SPN for AMS access (see Step 3)
    - **Media Services Storage Account Name**: specify your AMS storage account created at Step 2
    - **Cosmosdb Account Name**: specify your COSMOS DB account name created at Step 6
    - **Search Service Name**: specify your Azure Search account name created at Step 5

### 8. Create blob container

Create a container for importing media files in *mediaimportXXX* Azure Blob Storage account (*XXX* is random string).

### 9. Deploy sample media workflow into an Azure Logic App account

Create a Logic App account with sample media workflow in your subscription.

  <a href="https://portal.azure.com/#create/Microsoft.Template/uri/https%3A%2F%2Fraw.githubusercontent.com%2Fshigeyf%2Fai-digitalmedia%2Fmaster%2Fazuredeploy-logicapp-workflow.json" target="_blank"><img src="http://azuredeploy.net/deploybutton.png"/></a>  

  * Settings
    - **Logic App Name**: specify any name
    - **Function Deployment Resource Group**: Specify Resource Group used in Step 7
    - **Function Deployment Name**: Specify created Function App Name created in Step 7
    - **Source Asses Storage Account Name**: Specify created Storage Account Name created in Step 7
    - **IngestMedia Watch Container**: Specify container name created in Step 8

### 10. Deploy sample web portal for digital media library 

Create a Web App account with sample portal app code in your subscription.

  <a href="https://portal.azure.com/#create/Microsoft.Template/uri/https%3A%2F%2Fraw.githubusercontent.com%2Fshigeyf%2Fai-digitalmedia%2Fmaster%2Fazuredeploy-portal.json" target="_blank"><img src="http://azuredeploy.net/deploybutton.png"/></a>

  * Settings
    - **Site Name**: specify web site name
    - **Hostting Plan Name**: specify Linux App Service Plan Name
    - **Sku**: specify Linux App Service Plan pricing tier
    - **Worker Size**: *0* is enough for this demo
    - **Repo URL**: Use your github repo
    - **Branch**: Use your branch in your github repo
    - **Cosmosdb Account Name**: specify your COSMOS DB account name created at Step 6
    - **Search Service Name**: specify your Azure Search account name created at Step 5

## How to try this sample demo application

1. Open deployed web site http://*sitename*.azurewebsites.net/ (*) *sitename* is specified at Step 10.
2. When opening the web site, the message "Action Required" will be shown, then click "Start Service" button in the web site.
3. Click "Continue".
4. Upload video files into blob container specified at Step 8.
