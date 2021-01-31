# Cockpit 🚁
##### An app that alerts you to the status of all your AWS Pipelines

![Screenshot](screenshot.png)

## How it works
The app looks at AWS Code Pipeline for all profiles located in the `~/.aws/credentials` file and show them in a Status Bar application

## Installation
1. Ensure that you have the AWS CLI installed and configured
2. Download the executable
3. Run the executable
4. Click on the 🚁 icon in the status bar

## Link to Failure Logs
In the event that a pipeline does fail, simply click any row with a 🔴 icon and it will take you directly to the logs of why that deployment failed

## Note
This was very purpose built for my company's workflows. Some examples of this are assuming that everything is in `us-east-1`, all pipeline failures from from CodeDeploy, and all deployments are to EC2 instances. Pull requests are welcome to generalize this app for the wider community.
