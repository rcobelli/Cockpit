//
//  DateTimeView.swift
//  WorldTime
//
//  Created by Gabriel Theodoropoulos.
//  Copyright © 2020 AppCoda. All rights reserved.
//

import Cocoa
import SwiftyJSON

class MenuView: NSView, LoadableView, NSTableViewDelegate, NSTableViewDataSource  {
	
	@IBOutlet weak var tableView: NSTableView!
	@IBOutlet weak var titleLabel: NSTextField!
	
	
	var timer: Timer?
    
    override init(frame frameRect: NSRect) {
        super.init(frame: frameRect)
        _ = load(fromNIBNamed: "MenuView")
    }
    
    
    required init?(coder aDecoder: NSCoder) {
        super.init(coder: aDecoder)
    }
    
	@objc fileprivate func showDateAndTimeInfo() {
		tableView.reloadData()
	}
	
	func startTimer() {
		timer = Timer.scheduledTimer(timeInterval: 1.0, target: self, selector: #selector(showDateAndTimeInfo), userInfo: nil, repeats: true)
		timer?.fire()
		
		RunLoop.current.add(timer!, forMode: .common)
	}
	
	func stopTimer() {
		timer?.invalidate()
		timer = nil
	}
	
	func tableView(_ tableView: NSTableView, viewFor tableColumn: NSTableColumn?, row: Int) -> NSView? {
		if row >= pipelineData.count {
			return nil
		}
		
		if tableColumn == tableView.tableColumns[0] {
			let cell = NSTextField()
			cell.identifier = NSUserInterfaceItemIdentifier(rawValue: "my_id")
			cell.isBezeled = false
			cell.drawsBackground = false
			cell.isEditable = false
			cell.isSelectable = false
			cell.stringValue = pipelineData[row].accountName + ": " + pipelineData[row].pipelineName
			
			return cell
		} else {
			let cell = NSImageView()
			cell.identifier = NSUserInterfaceItemIdentifier(rawValue: "my_id")
			cell.image = NSImage(named: pipelineData[row].pipelineStatus.rawValue)
			return cell
		}
	}
	
	func tableView(_ tableView: NSTableView, shouldSelectRow row: Int) -> Bool {
		return pipelineData[row].pipelineStatus == .failed
	}
	
	@IBAction func changedSelection(sender: Any) {
		if pipelineData[tableView.selectedRow].failureLink != nil {
			let url = URL(string: pipelineData[tableView.selectedRow].failureLink!)!
			NSWorkspace.shared.open(url)
		}
	}
	
	func numberOfRows(in tableView: NSTableView) -> Int {
		return pipelineData.count
	}
	
	@IBAction func reloadData(sender: Any) {
		let center = NotificationCenter.default
		center.post(name: NSNotification.Name(rawValue: "Reload"), object: nil)
	}
}
