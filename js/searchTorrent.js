var pendingRequests = 0;
var totalRequests = 0;
var results = new Array();
var srcSettingCutBookTitlesAt = [ // defaults:
	':',
	'–',
	' -', // normal hyphen, don't match inside words
	'－',
	'(',
	'[',
	'{',
	'by',
	'⹀',
	'=',
	' "',
	" '",
];
var srcSettingCutBookTitles = false;
var cacheDuplicatesShort = []


$(document).ready(function() {
	$('#resultsContainer').parent().hide();
	$("#progressBar").parent().hide();

	$("#srcSettingCutBookTitlesAt").val(srcSettingCutBookTitlesAt.join("|"));

	$("form").bind("submit", function(event) {
		event.preventDefault();
	});

	// load available providers
	$.ajax({
		type : "GET",
		url : 'availableParsers.php',
		success : function(msg) {

			var providers = $.parseJSON(msg);
			for (var i = 0; i < providers.length; i++) {
				$("#prvList").append(
						"<li><input type='checkbox' checked='checked' value='"
								+ providers[i].className + "'> <span class='label' "
								+ getStyle(providers[i]) + ">" + providers[i].name
								+ "</span></li>");
			}

		},
	});

	// search button
	$("#srcButton").bind(
		"click",
		function(event) {
			event.preventDefault();

			$('#resultsContainer').parent().animate({
				height : 0,
				opacity : 0
			}, function() {
				$('#resultsContainer').find("tr:gt(0)").remove();
				results = new Array();
				$("#progressBar").parent().show();
				$("#progressBar").css("width", "100%");
			});
			$('#progressBar').html('Preparing search');
			
			var providers = $("#prvList li input:checked");
			if (
				   ($('#srcSettingCutBookTitles').prop('checked') == true)
				&& ($('#srcSettingCutBookTitlesAt').length > 0)
			)
			{
				srcSettingCutBookTitles = true;
				srcSettingCutBookTitlesAt = $('#srcSettingCutBookTitlesAt').val().split('|');
			}


			if ($('#srcMultiple').val() != '')
			{
				var searchTerms = $('#srcMultiple').val().split(/\r\n|\r|\n/);
				totalRequests = pendingRequests = searchTerms.length * providers.length;
				$('#progressBar').html('Preparing to parse ' + pendingRequests + ' searches');

				$.each(searchTerms, function(key, searchTerm) {
					setTimeout(
						searchTorrent(searchTerm, providers),
						50
					);
				});
			}
			else { // always default to single search:
				var searchTerm = $('#srcBox').val();
				totalRequests = pendingRequests = 1 * providers.length;

				searchTorrent(searchTerm, providers);
			}

		}
	);
});

function cutBookTitleAt(searchTerm) {
	$(srcSettingCutBookTitlesAt).each(function(key, cutWith) {
		
		newTerm = searchTerm.split(cutWith)[0];
		if (
			   (newTerm != searchTerm)
			&& (newTerm.length > 0)
		)
		{
			searchTerm = newTerm;
			return false; // break 
		}
	});
	
	return searchTerm;
}
		
function searchTorrent(searchTerm, providers) {
	if (srcSettingCutBookTitles == true)
	{
		searchTerm = cutBookTitleAt(searchTerm);
	}
	
	searchTerm = searchTerm.trim();
	
	if (
		   (searchTerm.length > 1)
		&& ($.inArray(searchTerm, cacheDuplicatesShort) == -1)
	)
	{
		cacheDuplicatesShort.push(searchTerm);

		for (var i = 0; i < providers.length; i++) {
			$.ajax({
				type : "POST",
				url : 'searchTorrents.php',
				data : 'keywords=' + searchTerm + '&p=' + providers[i].value,
				complete : function(msg, status) {
					pendingRequests -= 1;

					$("#progressBar").css(
						"width",
						(totalRequests - pendingRequests) * (100 / totalRequests) + "%"
					);
					$('#progressBar').html((totalRequests - pendingRequests) + '/' + totalRequests + ' done');
			
			
					if (status == "success" && msg.responseText != null
							&& msg.responseText != "") {
						results[pendingRequests] = $.parseJSON(msg.responseText);

						var sortedResults = sortResults(results);
						if (sortedResults != null && sortedResults.length > 0) {
							var formattedResults = formatResults(sortedResults);
							$('#resultsContainer').find("tr:gt(0)").remove();
							$('#resultsContainer').children().append(
									formattedResults);
							$('#resultsContainer').parent().show().animate({
								height : '100%',
								opacity : 1
							});
						}
					}

					// use timer to avoid outdated read of
					// variable
					setTimeout(function() {
						if (pendingRequests <= 0) {
							$("#progressBar").css("width", "100%");
							$('#progressBar').html('DONE');
							$("#progressBar").parent().fadeOut();
						}
					}, 500);
				},
			});

		}
	}
	else {
		pendingRequests -= providers.length;
	}

}

function formatResults(res) {

	if (res != null && res.length > 0) {
		var rows = "";
		for (var i = 0; i < res.length; i++) {

			var tor = getIconColumn(res[i].torrentLink,
					"title='Download using a torrent file.' class='glyphicon glyphicon-download'",
					true);
			var mag = getIconColumn(res[i].magnetLink,
					"title='Download using a magnet link.' class='glyphicon glyphicon-magnet'",
					false);
			var source = "<td><div class='label' " + getStyle(res[i]) + ">" + res[i].source
					+ "</div></a></td>";
			var name = "<td><div class='h4'>"
					+ ((res[i].sourceUrl != null) ? "<a href='" + res[i].sourceUrl + "'>"
							+ res[i].name + "</a>" : res[i].name) + "</br><small>"
					+ res[i].description + "</small></div></td>";

			rows += "<tr>" + tor + mag + source + name + "</tr>";
		}
	}
	return rows;

}

function getStyle(source) {
	var fgColor = 'white';
	var bgColor = 'gray';
	if (source != null) {
		fgColor = source.fgColor;
		bgColor = source.bgColor;
	}
	return " style='color: " + fgColor + "; background: " + bgColor + ";'";

}

function getIconColumn(link, divAttributes, isTorrent) {
	var linkType = "class='magnet'";
	if (isTorrent) {
		linkType = "class='torrent'";
	}

	if (link != null) {
		return "<td><a onclick='addTorrent(event);' href='" + link + "' " + linkType + "><div "
				+ divAttributes + "></div></a></td>";
	} else {
		return "<td><div " + divAttributes + " style='color:gray;'></div></td>";
	}
}

function addTorrent(event) {
	event.preventDefault();
	$.ajax({
		type : "POST",
		url : "requestDownload.php",
		data : 'link=' + encodeURIComponent(event.currentTarget.href) + "&type="
				+ event.currentTarget.className,
		complete : function(msg, status) {
			try {
				var res = $.parseJSON(msg.responseText);
				if (status == "success" && res.result == "success") {
					alert("Torrent " + res.arguments["torrent-added"].name
							+ " was added to the download queue!");
				} else {
					alert("Error: [" + status + "] " + " (" + msg.responseText + ")");
				}
			} catch (err) {
				alert("Error: [" + status + "] " + " (" + msg.responseText + ") " + err);
			}
		}
	});
}

function sortResults(results) {

	var keys = [];
	for ( var k in results)
		keys.push(k);

	var maxLength = 0;
	for ( var key in keys) {
		if (results[key] != null && results[key].length > maxLength)
			maxLength = results[key].length;
	}

	var sorted = [];
	for (var i = 0; i < maxLength; i++) {
		for (var prov = 0; prov < keys.length; prov++) {
			if (results[prov] != null && i < results[prov].length)
				sorted.push(results[prov][i]);
		}
	}

	return sorted;
}
