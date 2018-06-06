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
	 	totalNonOptiItems = 0,
		totalItemsToProcess = 0,
		toProcess = 0,
		errorCount = 0,
		awaitingOpti, allfiles;
	
	function getList(ajxaction) {
		$('#bulkOptimizeOutputProgressPercent').html("<p><b>Retrieving files list - Please wait</b></p>");
			/*var call = $.ajax({
				url: ajaxurl,
				data: {action: ajxaction},
				type: 'get',
			});
			
			call.done(function(fileids) {
				processList(fileids);
			});*/
			var fileids;
			
			$.ajax({
				url: ajaxurl,
				data: {action: 'get_full_files_list'},
				type: 'get'
			}).done( function( data ) {
				console.log(data);
				if (data.all) allfiles = data.all;
				if (data.nonopti) awaitingOpti = data.nonopti;
				totalItems = allfiles.length;
				totalNonOptiItems = awaitingOpti.length;
				if (ajxaction == 'get_nonopti_files_list') fileids = awaitingOpti;
				if (ajxaction == 'get_all_files_list') fileids = allfiles;
				processList(fileids);
			});
			/*
			var j1 = $.ajax( {
						url: ajaxurl,
						data: {action: 'get_all_files_list'},
						type: 'get'
					}).done( function( data ) {
						console.log("ALL FILES" + data.length);
						console.log(data);
						totalItems = data.length;
						if (ajxaction == 'get_all_files_list') fileids = data;
					});
				/*$.ajax({ // First Request
				url: ajaxurl,
				data: {action: 'get_all_files_list'},
				type: 'get',
				success: function(data) {
					totalItems = data.length;
					if (ajxaction == 'get_all_files_list') fileids = data;
				}      
			}); */
			/*
			var j2 = $.ajax( {
						url: ajaxurl,
						data: {action: 'get_nonopti_files_list'},
						type: 'get'
					}).done( function( data ) {
						console.log("NON OPTI FILES" + data.length);
						console.log(data);
						totalNonOptiItems = data.length;
						awaitingOpti = data;
						if (ajxaction == 'get_nonopti_files_list') fileids = data;
					});
					*/
				 /*$.ajax({ //Seconds Request
				url: ajaxurl,
				data: {action: 'get_nonopti_files_list'},
				type: 'get',
				success: function(data) {
					totalNonOptiItems = data.length;
					awaitingOpti = data;
					if (ajxaction == 'get_nonopti_files_list') fileids = data;
				}  
			}); */
			/*
			$.when(j1, j2).then(function() {
				console.log(fileids);
				processList(fileids);
			});*/

	}
	
	function processList(idslist) {
		if (idslist) {
			toProcess = totalItemsToProcess = idslist.length;
			$('#bulkOptimizeOutputProgressPercent').html( totalItemsToProcess +" files in queue - Please wait");
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
							setCircleProgress(oData);
						}).fail(function(oData) { 
							console.log(oData); 
							toProcess--;
							errorCount++;
							setCounter(null);
							setCircleProgress(idslist[i], oData);
						});
					/*** PROCESS SINGLE FILE ***/	
				}
		} else console.log('no data');
	}
	
	function setCounter(datas) {
		var fileCount = totalItemsToProcess - toProcess;
		if (fileCount == toProcess) {
			$('#bulkOptimizeOutputNotice').html('Optimization completed');
			$('#bulkOptimizeOutputProgressPercent').html('100%');
			$('#bulkOptimizeOutputProgress > span').css('width', '100%');
		} else {
			var message = 'An error occured while processing file '+ fileCount +' of '+ totalItemsToProcess;
			if (datas) message = 'File '+ fileCount +' of '+ totalItemsToProcess +' successfully optimized : '+ datas['image_optimizer'];
			$('#bulkOptimizeOutputNotice').html(message);
			var percentProgress = (fileCount / totalItemsToProcess * 100).toFixed(2);
			$('#bulkOptimizeOutputProgressPercent').html(percentProgress + '%');
			$('#bulkOptimizeOutputProgress > span').css('width', percentProgress + '%');
			if ( percentProgress == 100.00) {
				$('#bulkOptimizeOutputProgressPercent').html('Optimization completed');
				$('#bulkOptimizeOutputNotice').html('<b>Congratulations, all your files have been optimized</b>');
				$('#bulkOptimizeWarning').remove();
			}
		}
	}
	
	function setCircleProgress(datas) {
		var idFile = datas['id'],
			idx = $.inArray(idFile, awaitingOpti);
		if (idx != -1) {
			awaitingOpti.splice(idx, 1);
			totalNonOptiItems--;
			var totalOptiItems = totalItems - totalNonOptiItems,
				optimizedPercent = totalOptiItems / totalItems * 100;
			
			$('#percentCircle').attr('class', 'c100 p'+ optimizedPercent.toFixed() +' big');
			$('#percentCircleValue').html( optimizedPercent.toFixed(2) +'%');
			$('#wpio-nonopti').html(totalNonOptiItems);
			if ( optimizedPercent.toFixed(2) == 100.00) $('#wpio-nonopti-row').remove();
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
		$('#bulkOptimizeButtons').remove();
		$('#bulkOptimize').show();
		getList('get_all_files_list');
	});
	$('#bulkOptimizeFiles').on( "click", function() {
		$('#bulkOptimizeButtons').remove();
		$('#bulkOptimize').show();
		getList('get_nonopti_files_list');
	});
	
})( jQuery );
