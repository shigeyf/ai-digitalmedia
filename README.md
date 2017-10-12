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

### 3. Create a Service Principal

Create a Service Principal and save the password. It will be needed in step 6.
To do so, go to the API tab in the account ([follow this article](https://docs.microsoft.com/en-us/azure/media-services/media-services-portal-get-started-with-aad#service-principal-authentication))

### 4. Make sure the AMS streaming endpoint is started

To enable streaming, go to the Azure portal, select the Azure Media Services account which has been created, and start the default streaming endpoint.

### 5. Create an Azure Search Service account

TO BE DOCUMENTED

### 6. Create an Azure COSMOS Database account

TO BE DOCUMENTED

### 7. Deploy sample media functions into an Azure Functions account

Create a Function account with this sample code of media functions in your subscription.

  <a href="https://portal.azure.com/#create/Microsoft.Template/uri/https%3A%2F%2Fraw.githubusercontent.com%2Fshigeyf%2Fai-digitalmedia%2Fmaster%2Fazuredeploy-functions.json" target="_blank"><img src="http://azuredeploy.net/deploybutton.png"/></a>  

  * This deployment script will not create an Azure Media Services account and an Azure Storage account 
  * Please consider Consumption Plan or App Service Plan if you will deploy manually
    * Consumption Plan – Timeout of function will be 5 mins
    * App Service Plan (Dedicated Plan) – There is no timeout (if AlwaysOn is enabled)
  * If a deployment target resource group already contains an App Service Plan (Dedicated Plan), Azure Functions app will be contained in that App Service Plan (Dedicated Plan)

### 8. Deploy sample media workflow into an Azure Logic App account

Create a Logic App account with sample media workflow in your subscription.

  <a href="https://portal.azure.com/#create/Microsoft.Template/uri/https%3A%2F%2Fraw.githubusercontent.com%2Fshigeyf%2Fai-digitalmedia%2Fmaster%2Fazuredeploy-logicapp-workflow.json" target="_blank"><img src="http://azuredeploy.net/deploybutton.png"/></a>  

### 9. Deploy sample web portal for digital media library 

Create a Web App account with sample portal app code in your subscription.

  <a href="https://portal.azure.com/#create/Microsoft.Template/uri/https%3A%2F%2Fraw.githubusercontent.com%2Fshigeyf%2Fai-digitalmedia%2Fmaster%2Fazuredeploy-portal.json" target="_blank"><img src="http://azuredeploy.net/deploybutton.png"/></a>


## How to try this sample demo application

TO BE DOCUMENTED

