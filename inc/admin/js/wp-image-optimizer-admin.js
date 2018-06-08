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
		awaitingOpti,
		optilist,
		allfiles,
		deferred,
		deferred2;
		
	
	function getList(ajxaction) {
		$('#bulkOptimizeOutputProgressPercent').html("<p><b>Retrieving files list - Please wait</b></p>");
			
		deferred = $.Deferred();
		deferred2 = $.Deferred();
		allfiles = [];
		optilist = [];
		
		get_full_list(0);
		get_opti_list(0);
		
		
		deferred.done(function() { console.log('done deferred');});
		deferred2.done(function() { console.log('done deferred2');});
		
		$.when( deferred, deferred2 ).done(function () {
			$('#bulkOptimizeOutputProgressPercent').html("<p><b>Building optimization queue - Please wait</b></p>");
			totalItems = allfiles.length;
			allfiles = Array.from(new Set(allfiles));

			optilist = Array.from(new Set(optilist));

			awaitingOpti = $(allfiles).not(optilist).get();
			totalNonOptiItems = awaitingOpti.length;

			if (ajxaction == 'get_nonopti_files_list') processList(awaitingOpti);
			if (ajxaction == 'get_all_files_list') processList(allfiles);
		});
		
	}
	
	
	function get_full_list(lastid) {
			$.ajax({
				url: ajaxurl,
				data: {action: 'get_full_list', lastid: lastid},
				type: 'post'
			}).success( function( data ) {
				console.log('FULL LIST');
				console.log( data);
				var idlast = data['lastid'],
					tmp = allfiles;
				allfiles = tmp.concat(data['data']);
				console.log( allfiles);
				if (idlast == lastid) {
					deferred.resolve();
					return;
				}
				get_full_list(idlast);
			});
	}
	
	function get_opti_list(lastid) {
			$.ajax({
				url: ajaxurl,
				data: {action: 'get_opti_list', lastid: lastid},
				type: 'post'
			}).success( function( data ) {
				console.log('OPTI LIST');
				console.log( data);
				var idlast = data['lastid'],
					tmp = optilist;
				optilist = tmp.concat(data['data']);
				console.log( optilist);
				if (idlast == lastid) {
					deferred2.resolve();
					return;
				}
				get_opti_list(idlast);
			});
	}
	
	
	function buildNonOptiList(optiList) {
		var tmp = [];
		for (var i = 0; i < totalItems; i++) {
   			if(!jQuery.inArray(allfiles[i], optiList) !== -1) tmp.push[allfiles[i]];
		}
		return tmp;
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
