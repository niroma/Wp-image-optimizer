(function( $ ) {
	'use strict';
	

	var mAjaxQueues = {}; // map for all ajaxQueues we are going to create
	jQuery.ajaxQueue = function(sUrl, oOptions, sQueueName) {
		if(typeof sUrl==='object') {
			sQueueName = oOptions;
			oOptions = sUrl;
			sUrl = undefined;
		}
		if(typeof oOptions==='string') {
			sQueueName = oOptions;
			oOptions = undefined;
		}
		
		// Force options to be an object, use default queue if no queue i given
		oOptions = oOptions||{};
		sQueueName = sQueueName||'default';
		
		// jQuery on an empty object, we are going to use this as our queue
		var oAjaxQueue = mAjaxQueues[sQueueName]||(mAjaxQueues[sQueueName]=jQuery({}));
		
		var oXHR, oDeferred = jQuery.Deferred(), oPromise = oDeferred.promise();
		function fnRequest(oNext) { (oXHR=jQuery.ajax(sUrl,oOptions))
			.done(oDeferred.resolve).fail(oDeferred.reject).then(oNext,oNext); }
		oAjaxQueue.queue(fnRequest);
		
		// Add the abort method to the promise
		oPromise.abort = function(sStatusText) {
			if(oXHR) return oXHR.abort(sStatusText);
			
			// if there wasn't already a jqXHR we need to remove from queue
			var aQueue = oAjaxQueue.queue(), iIndex = jQuery.inArray(fnRequest, aQueue);
			if(iIndex>-1) aQueue.splice(index, 1);
			
			oDeferred.rejectWith(oOptions.context||oOptions, [oPromise,sStatusText,'']);
			return oPromise;
		};
		
		return oPromise;
	};
	

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
         * 
         * The file is enqueued from inc/admin/class-admin.php.
	 */

	var totalItems = 0,
		toProcess = 0,
		errorCount = 0;
	
	function getList(ajxaction) {
		$('#bulkOptimizeOutputProgressPercent').html("<p><b>Retrieving files list - Please wait</b></p>");
		var call = $.ajax({
			url: ajaxurl,
			data: {action: ajxaction},
			type: 'get',
		});
		
		call.done(function(fileids) {
			processList(fileids);
		});
	}
	
	function processList(idslist) {
		if (idslist) {
			toProcess = totalItems = idslist.length;
			$('#bulkOptimizeOutputProgressPercent').html( totalItems +" files found - Please wait");
				for (var i in idslist) {
					/*** PROCESS SINGLE FILE ***/
						$.ajaxQueue({
							url: ajaxurl,
							data: {action: 'image_optimizer_optimize_file', file: idslist[i] },
							type: 'post'
						}).done(function(oData) { 
							console.log(oData); 
							toProcess--;
							setCounter(oData);
						}).fail(function(oData) { 
							console.log(oData); 
							toProcess--;
							errorCount++;
							setCounter(null);
						});
					/*** PROCESS SINGLE FILE ***/	
					//$('#bulkOptimizeOutputProgressPercent').html('Optimization Completed');
					//$('#bulkOptimizeOutputProgress > span').css('width', '100%');
				}
		} else console.log('no data');
	}
	
	function setCounter(datas) {
		var fileCount = totalItems - toProcess;
		if (fileCount == toProcess) {
			$('#bulkOptimizeOutputNotice').html('Optimization completed');
			$('#bulkOptimizeOutputProgressPercent').html('100%');
			$('#bulkOptimizeOutputProgress > span').css('width', '100%');
		} else {
			var message = 'An error occured while processing file '+ fileCount +' of '+ totalItems;
			if (datas) message = 'File '+ fileCount +' of '+ totalItems +' successfully optimized : '+ datas['image_optimizer'];
			$('#bulkOptimizeOutputNotice').html(message);
			var percentProgress = (fileCount / totalItems * 100).toFixed(2);
			$('#bulkOptimizeOutputProgressPercent').html(percentProgress + '%');
			$('#bulkOptimizeOutputProgress > span').css('width', percentProgress + '%');
		}
	}
	/*
	function processFile(filename, itemcount){
		var def = $.Deferred();
		
		
		processing = $.ajax({
			url: ajaxurl,
			data: {action: 'image_optimizer_optimize_file', file: filename },
			type: 'post',
			success: function(data) {
				console.log(data);
				printProgress(itemcount);
				def.resolve();
			},
			error: function(data) {
				console.log(data);
				printProgress(itemcount);
				def.resolve();
			}
		});
		processing.done(function(fileids) {
			return def.promise();
		});
	}
	
	function printProgress(itemcount) {
		$('#bulkOptimizeOutputNotice').html('Processing file '+ itemcount);
		var percentProgress = (itemcount / toProcess * 100).toFixed(2);
		$('#bulkOptimizeOutputProgressPercent').html(percentProgress + '%');
		$('#bulkOptimizeOutputProgress > span').css('width', percentProgress + '%');	
	}
	*/
	$('#bulkOptimizeAllFiles').on( "click", function() {
		$('#bulkOptimizeButtons').hide();
		$('#bulkOptimize').show();
		getList('get_all_files_list');
	});
	$('#bulkOptimizeFiles').on( "click", function() {
		$('#bulkOptimizeButtons').hide();
		$('#bulkOptimize').show();
		getList('get_nonopti_files_list');
	});
	
})( jQuery );
