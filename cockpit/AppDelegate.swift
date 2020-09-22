//
//  AppDelegate.swift
//  cockpit
//
//  Created by Ryan Cobelli on 9/21/20.
//

import Cocoa
import SwiftyJSON


var pipelineData = [Pipeline]()

@NSApplicationMain
class AppDelegate: NSObject, NSApplicationDelegate {
	@IBOutlet weak var menu: NSMenu?
	@IBOutlet weak var firstMenuItem: NSMenuItem?
	
	var statusItem: NSStatusItem?
	var dateTimeView: MenuView?
	var dataLoadingInProgress = false
	
	override func awakeFromNib() {
		super.awakeFromNib()
		
		statusItem = NSStatusBar.system.statusItem(withLength: NSStatusItem.variableLength)
		let itemImage = NSImage(named: "icon")
		itemImage?.isTemplate = true
		statusItem?.button?.image = itemImage
		
		if let menu = menu {
			statusItem?.menu = menu
			menu.delegate = self
		}
		
		if let item = firstMenuItem {
			dateTimeView = MenuView(frame: NSRect(x: 0.0, y: 0.0, width: 275.0, height: 250.0))
			item.view = dateTimeView
		}
		
		let center = NotificationCenter.default
		center.addObserver(forName: nil, object: nil, queue: nil) { notification in
			if notification.name == NSNotification.Name(rawValue: "Reload") {
				self.getData()
			}
		}
		
		getData()
	}
	
	func getData() {
		if !dataLoadingInProgress {
			dataLoadingInProgress = true
			pipelineData = [Pipeline]()
			DispatchQueue.global(qos: .userInitiated).async(execute: {
				
				var profiles = self.shell("cat ~/.aws/credentials | grep \"^\\[\"").components(separatedBy: "\n")
				profiles.removeLast()
				for i in 0..<profiles.count {
					profiles[i].removeLast()
					profiles[i].removeFirst()
				}
				
				// Loop through all AWS accounts
				for profile in profiles {
					// Get the account ID (ex: 123412341234)
					let accountID = JSON(parseJSON: self.shell("/usr/local/bin/aws sts get-caller-identity --profile " + profile))["Account"].string
					
					// List all pipelines on the account
					let pipelines = JSON(parseJSON: self.shell("/usr/local/bin/aws codepipeline list-pipelines --profile " + profile))["pipelines"]
					for pipeline in pipelines {
						// Get the pipeline name
						let name = pipeline.1["name"].string
						
						
						var status = PipelineStatus.success
						var failureURL : String?
						
						// Get a list of all stages
						let stages = JSON(parseJSON: self.shell("/usr/local/bin/aws codepipeline get-pipeline-state --name " + name! + " --profile " + profile))["stageStates"]
						
						// Loop through all stages
						for stage in stages {
							if stage.1["latestExecution"]["status"] == "Failed" {
								let deploymentID = stage.1["actionStates"][0]["latestExecution"]["externalExecutionId"].string
								let instanceID = JSON(parseJSON: self.shell("/usr/local/bin/aws deploy list-deployment-instances --deployment-id " + deploymentID! + " --profile " + profile))["instancesList"][0].string
								failureURL = "https://console.aws.amazon.com/codesuite/codedeploy/deployments/" + deploymentID! + "/instances/arn%3Aaws%3Aec2%3Aus-east-1%3A" + accountID! + "%3Ainstance/" + instanceID! + "/logs/AfterInstall?region=us-east-1"
								status = .failed
								break
							} else if stage.1["latestExecution"]["status"] == "InProgress" {
								status = .inProgress
								break
							}
						}
						
						// Pull data from the source stage
						let timestamp = stages[0]["actionStates"][0]["latestExecution"]["lastStatusChange"].string
						let commitMessage = stages[0]["actionStates"][0]["latestExecution"]["summary"].string
						
						let tmp = Pipeline(accountName: profile,
										   accountID: accountID!,
										   pipelineName: name!,
										   pipelineStatus: status,
										   timestamp: timestamp!,
										   commitMessage: commitMessage!,
										   failureLink: failureURL)
						pipelineData.append(tmp)
					}
				}
				self.dataLoadingInProgress = false
			})
		}
	}
	
	func shell(_ command: String) -> String {
		let task = Process()
		let pipe = Pipe()
		
		task.standardOutput = pipe
		task.arguments = ["-c", command]
		task.launchPath = "/bin/sh"
		task.launch()
		
		let data = pipe.fileHandleForReading.readDataToEndOfFile()
		let output = String(data: data, encoding: .utf8)!
		
		return output
	}
}

extension AppDelegate: NSMenuDelegate {
	func menuWillOpen(_ menu: NSMenu) {
		dateTimeView?.startTimer()
	}
	
	func menuDidClose(_ menu: NSMenu) {
		dateTimeView?.stopTimer()
	}
}

