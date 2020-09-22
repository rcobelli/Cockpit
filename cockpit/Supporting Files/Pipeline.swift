//
//  Pipeline.swift
//  cockpit
//
//  Created by Ryan Cobelli on 9/21/20.
//

import Foundation

struct Pipeline {
	var accountName: String
	var accountID: String
	var pipelineName: String
	var pipelineStatus: PipelineStatus
	var timestamp: String
	var commitMessage: String
	var failureLink: String?
}

enum PipelineStatus: String {
	case success = "success"
	case inProgress = "inprogress"
	case failed = "failed"
}
