---
services: media-services,functions, comsmosdb, search
platforms: dotnet, python
author: shigeyf, yoichika
---

# ai-digitalmedia
AI powered digitial media library portal - A example web portal application on Microsoft Azure cloud platform.

The demo session shall focus on:
 * Transforming customer’s digital media assets on-prem into Azure
 * Driving media intelligence leveraged by Video AI
 * Online Video Portal (OVP) with indexing and search capability

# Azure Workloads used in this demo:
 - Hybrid Storage (StorSimple)
 - StorSimple Data Transformation Manager
 - Azure Media Services
 - Azure Media Analytics
 - Azure Functions
 - Azure Logic Apps
 - Azure Search
 - Azure Web App for Container
 - Cosmos DB

# How to deply this sample

1. Deploy Azure Media Services
2. Fork https://github.com/shigeyf/ai-digitalmedia/ to your own repo
3. Deploy Azure Functions  

  <a href="" target="_blank"><img src="http://azuredeploy.net/deploybutton.png"/></a>  

  * This deployment script will not create an Azure Media Services account and an Azure Storage account 
  * Please consider Consumption Plan or App Service Plan if you will deploy manually
    * Consumption Plan – Timeout of function will be 5 mins
    * App Service Plan (Dedicated Plan) – There is no timeout (if AlwaysOn is enabled)
  * If a deployment target resource group already contains an App Service Plan (Dedicated Plan), Azure Functions app will be contained in that App Service Plan (Dedicated Plan)

4. Deploy Azure Logic Apps

  <a href="" target="_blank"><img src="http://azuredeploy.net/deploybutton.png"/></a>  
