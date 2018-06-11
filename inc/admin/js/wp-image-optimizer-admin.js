(function( $ ) {
	'use strict';

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

	var filesSum = 0,
		optiFilesSum = 0,
		nonOptiFilesSum = 0,
		optiPercent = 0,
		filesSize = 0,
		OptiFilesSize = 0,
		savedSpace = 0,
		averageSizeReduction = 0,
		totalItems = 0,
	 	totalNonOptiItems = 0,
		totalItemsToProcess = 0,
		toProcess = 0,
		errorCount = 0,
		awaitingOpti,
		optilist,
		allfiles,
		deferred,
		deferred2,
		currentQueue;
		
		
		/*
		$this->loader->add_action( 'wp_ajax_get_files_sum', $plugin_admin, 'get_files_sum' );
		$this->loader->add_action( 'wp_ajax_get_optimized_files_sum', $plugin_admin, 'get_optimized_files_sum' );
		$this->loader->add_action( 'wp_ajax_get_optimized_total_size', $plugin_admin, 'get_optimized_total_size' );
		$this->loader->add_action( 'wp_ajax_get_original_total_size', $plugin_admin, 'get_original_total_size' );
		
	    $total = $this->get_files_sum(false);
		if ($total > 0) {
			$optimized = $this->get_optimized_files_sum();
			$nonoptimized = $total - $optimized;
			$optimizedPercent = round($optimized / $total * 100,2);
		}
		
		$totalsize = $this->get_original_total_size();
		$optimizedsize = $this->get_optimized_total_size();
		$spacesaved = $totalsize - $optimizedsize;
		if ($totalsize > 0) $averagesizereduction = round($spacesaved / $totalsize * 100,2);	
		*/
	
	function countFiles() {
		$.ajax({
			url: ajaxurl,
			data: {action: 'get_files_sum'},
			type: 'get'
		}).success( function( data ) {
			console.log( data);
			if (data != 0) {
				filesSum = data;
				countOptiFiles();
				getTotalSize();
			}
			//$('#wpio_count_row').html( '<b>'+ filesSum +'</b> images found in your media library');
			$('#imagesFound').removeClass('loading').html('<b>'+ filesSum +'</b>');
		});
	}
	
	function countOptiFiles() {
		
		$.ajax({
			url: ajaxurl,
			data: {action: 'get_optimized_files_sum'},
			type: 'get'
		}).success( function( data ) {
			console.log('FULL LIST');
			console.log( data);
			if (data != 0) {
				optiFilesSum = data;
				nonOptiFilesSum = filesSum - optiFilesSum;
				optiPercent = optiFilesSum / filesSum * 100;
				
				$('#wpio_opti_row').html('<span id="wpio-nonopti">'+ nonOptiFilesSum +'</span> files need an optimization');
				if ( optiPercent.toFixed(2) == 100.00) {
					$('#bulkOptimizeFilesCol').remove();
					$('#alien').removeClass('wow cry loading').addClass('happy');
					$('#wpio_opti_row').html('Congratulations ! All images are optimized :)');
				} else {
					$('#bulkOptimizeFilesCol').show();
					if ( optiPercent < 15 ) $('#alien').removeClass('happy wow loading').addClass('cry');
					else $('#alien').removeClass('happy cry loading').addClass('wow');
				}
				
                $('#imagesOpti').removeClass('loading').html('<b>'+ optiPercent.toFixed(2) +'%</b>');
				
			}
		});
	}
	
	function getTotalSize() {
		$.ajax({
			url: ajaxurl,
			data: {action: 'get_original_total_size'},
			type: 'get'
		}).success( function( data ) {
			console.log('FULL LIST');
			console.log( data);
			if (data != 0) {
				filesSize = data;
				getOptiSize();
			}
		});
	}
	
	function getOptiSize() {
		$.ajax({
			url: ajaxurl,
			data: {action: 'get_optimized_total_size'},
			type: 'get'
		}).success( function( data ) {
			console.log('FULL LIST');
			console.log( data);
			if (data != 0) {
				OptiFilesSize = data;
				savedSpace = filesSize - OptiFilesSize;
				if (filesSize > 0) averageSizeReduction = savedSpace / filesSize * 100;
				
                $('#savedSpace').removeClass('loading').html('<b>'+ formatBytes(savedSpace) +'</b>');
                $('#avgReduction').removeClass('loading').html('<b>'+ averageSizeReduction.toFixed(2) +'%</b>');
			}
		});
	}
	
	function formatBytes(bytes,decimals) {
	   if(bytes == 0) return '0 Bytes';
	   var k = 1024,
		   dm = decimals || 2,
		   sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'],
		   i = Math.floor(Math.log(bytes) / Math.log(k));
	   return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
	}
	
	function getList(ajxaction) {
		$('#bulkOptimizeOutputProgressPercent').html("Retrieving files list - Please wait");
			
		deferred = $.Deferred();
		deferred2 = $.Deferred();
		allfiles = [];
		optilist = [];
		
		get_full_list(0);
		get_opti_list(0);
	
		deferred.done(function() { console.log('done deferred');});
		deferred2.done(function() { console.log('done deferred2');});
		
		$.when( deferred, deferred2 ).done(function () {
			$('#bulkOptimizeOutputProgressPercent').html("Building optimization queue - Please wait");
			totalItems = allfiles.length;
			allfiles = Array.from(new Set(allfiles));

			optilist = Array.from(new Set(optilist));

			awaitingOpti = $(allfiles).not(optilist).get();
			totalNonOptiItems = awaitingOpti.length;
			
			optilist = [];

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
	
	function processList(idslist) {
		if (idslist) {
			toProcess = totalItemsToProcess = idslist.length;
			currentQueue = idslist.slice(0);
			$('#bulkOptimizeOutputProgressPercent').html( totalItemsToProcess +" files in queue - Please wait");
			optimizeFile();
		} else console.log('no data');
	}

	function optimizeFile() {
		console.log('Optimizing single files now !');
		if ( currentQueue ) {
			//var idfile = currentQueue[0];
			var idfile = currentQueue.shift();
			$.ajax({
				url: ajaxurl,
				data: {action: 'image_optimizer_optimize_file', file: idfile },
				type: 'post'
			}).done(function(oData) { 
				console.log(oData); 
				setCounter(oData);
			}).fail(function(oData) { 
				console.log(oData); 
				errorCount++;
				setCounter(null);
			}).always(function () {
				toProcess--;
				setCircleProgress(idfile);
				//currentQueue.shift();
				if (currentQueue.length != 0) {
					optimizeFile();	
				}
     		});
		}
	}

	function setCounter(datas) {
		if (datas) {
			var fileCount = totalItemsToProcess - toProcess;
			if (fileCount == toProcess) {
				$('#bulkOptimizeOutputNotice').html('Optimization completed');
				$('#bulkOptimizeOutputProgressPercent').html('100%');
				$('#bulkOptimizeOutputProgress > span').css('width', '100%');
				$('#alien').removeClass('wow cry loading').addClass('happy');
			} else {
				var message = 'An error occured while processing file '+ fileCount +' of '+ totalItemsToProcess;
				if (datas) message = 'File '+ fileCount +' of '+ totalItemsToProcess +' successfully optimized : '+ datas['image_optimizer'];
				$('#bulkOptimizeOutputNotice').html(message);
				var percentProgress = (fileCount / totalItemsToProcess * 100).toFixed(2);
				$('#bulkOptimizeOutputProgressPercent').html(percentProgress + '%');
				$('#bulkOptimizeOutputProgress > span').css('width', percentProgress + '%');
				if ( percentProgress == 100.00) {
					$('#alien').removeClass('wow cry loading').addClass('happy');
					$('#bulkOptimizeOutputProgressPercent').html('Optimization completed');
					$('#wpio_opti_row').html('Congratulations ! All images are optimized :)');
					$('#bulkOptimizeWarning').remove();
				}
			}
		}
	}
	
	function setCircleProgress(idFile) {
		if (idFile) {
			//console.log('set progress for '+ idFile);
			var idx = $.inArray(idFile, awaitingOpti);
			if (idx != -1) {
				awaitingOpti.splice(idx, 1);
				totalNonOptiItems--;
				var totalOptiItems = totalItems - totalNonOptiItems,
					optimizedPercent = totalOptiItems / totalItems * 100;
				
				//$('#percentCircle').attr('class', 'c100 p'+ optimizedPercent.toFixed() +' big');
				//$('#percentCircleValue').html( optimizedPercent.toFixed(2) +'%');
				$('#imagesOpti').removeClass('loading').html('<b>'+ optimizedPercent.toFixed(2) +'%</b>');
				
				$('#wpio_opti_row #wpio-nonopti').html(totalNonOptiItems);
				if ( optimizedPercent.toFixed(2) == 100.00) if ( optiPercent.toFixed(2) == 100.00) $('#wpio_opti_row').html('<b>Congratulations ! All images are optimised :)</b>');
			} else console.log(idFile + ' not awaiting opti ');
		} else console.log('idFile is empty');
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
	
	countFiles();
	
})( jQuery );
