/* eslint-disable */
"use strict";

jQuery(document).ready(function() {
	var $ = jQuery;
	var baseURL = window.pb_baseUrl,
		page_id,
		type,
		_id;



	function checkBackup() {
		return new Promise(function(resolve) {
			$.get("index.php?pb3ajax=1&task=checkBackup").complete(function(result) {
				console.log('Backed up:',Number(result.responseJSON));
				if (Number(result.responseJSON) > 0) {
					resolve(true);
				} else {
					resolve(false);
				}
			});
		});
	}

	checkBackup().then(function(haveBackup) {
		$('.pb-convert').length === 0 && $('#convert-confirm').addClass('hidden')
		if (haveBackup) {
			$("#convert-backup").removeClass("hidden");
			$("#convert-backup").on("click", function(e) {
				if (confirm("Are you sure to revert your backup data?")) {
					runRevert().then(function(result) {
						alert("Successfully revert all your backup data!");
						$("#convert-backup").addClass("hidden");
						window.location.reload();
					});
				}
			});
		}
	});

	function runRevert() {
		return new Promise(function(resolve) {
			$.get("index.php?pb3ajax=1&task=revertBackUp").complete(function(result) {
				resolve(result.responseJSON);
			});
		});
	}

	$("#convert-confirm").on("click", function(e) {
		run();
	});
	function run() {
		var convertAreas = $(".pb-convert");

		if (convertAreas.length > 0) {
			$("#convert-progress").removeClass("hidden");
		}

		var progress = 0;
		var index = 0;

		function runConvert() {
			progress = (index + 1) / convertAreas.length * 100;
			$("#convert-view-label").html(
				"Converting... " + index + " / " + convertAreas.length + " items"
			);

			if (index < convertAreas.length) {
				var tArea = convertAreas[index];
				page_id = $(tArea)
					.data("pageid")
					.toString();
				type = $(tArea).data("pagetype");
				_id = $(tArea).attr("id");
				window.pagefly_data = {
					baseURL: baseURL,
					page_id: page_id,
					_id: _id,
					convert_mode: true
				};

				getArticleContent(page_id, "content").then(function(value) {
					var props = value.match(
						/<!-- Start PageBuilder Data\|(.*)\|End PageBuilder Data -->/
					);
					initPageBuilder(page_id, props, _id).then(function(pagefly) {
						var page = pagefly.pages[_id];
						console.log(page)
						page.renderHTML(function(html) {
							if (html) {
								var data = JSON.stringify(page.data);
								var output = "<!-- Start New PageBuilder HTML -->";
								output += html;
								output += "<!-- End New PageBuilder HTML -->";
								output += "<!-- Start New PageBuilder Data|";
								output += btoa(unescape(encodeURIComponent(data)));
								output += "|End New PageBuilder Data -->";
								runBackup(page_id, type).then(function(backed) {
									if (backed && index == convertAreas.length - 1) {
										$("#convert-backup").removeClass("hidden");
									}
									saveData(output, page_id, type).then(function(result) {
										if (result == false) {
											console.log("Error when save data: ", type, page_id);
										}
										++index;
										$("#convert-view-progress-bar").css(
											"width",
											progress + "%"
										);
										$("#convert-view-progress-bar").html(progress + "%");
										if (progress === 100) {
											$("#convert-view-progress-bar").addClass("bar-success");
										}
										setTimeout(function() {
											console.log("Next.....");
											runConvert();
										}, 1000);
									});
								});
							}
						});
					});
				});
			}
		}

		if (convertAreas.length > 0) {
			$("#convert-view-label").html(
				"Converting... " + index + " / " + convertAreas.length + " items"
			);
			runConvert();
		} else {
			$("#convert-view-label").html("Nothing to convert...");
		}
	}


	function getArticleContent(id, type) {
		return new Promise(function(resolve) {
			$.get(
				"index.php?pb3ajax=1&task=getContentByID&id=" + id + "&type=" + type
			).complete(function(result) {
				resolve(result.responseJSON);
			});
		});
	}

	function initPageBuilder(page_id, props, _id) {
		return new Promise(function(resolve) {
			requirejs(["pagefly"], function(pagefly) {
				try {
					props = props
						? JSON.parse(decodeURIComponent(escape(atob(props[1]))))
						: {};
				} catch (e) {
					props = {};
				} finally {
					props.textArea = document.getElementById(_id);
					props.page_id = page_id;
					props._id = _id;
					props.massConvert = true
					pagefly.setPublicPath(
						baseURL + "plugins/editors/pagebuilder3/assets/app/"
					);
					pagefly
						.init(
							props,
							$('<div id="pb_editor_' + page_id + '" class="hidden">')
								.insertAfter("#" + _id)
								.get(0)
						)
						.then(function(page) {
							console.log(page)
							resolve(window.pb);
							$('#pagefly-pb-app').addClass('hidden')
						});

					$("#" + _id).addClass("hidden");
				}
			});
		});
	}

	function runBackup(id, type) {
		return new Promise(function(resolve) {
			$.get(
				"index.php?pb3ajax=1&task=backupContent&id=" + id + "&type=" + type
			).complete(function(result) {
				resolve(result.responseText);
			});
		});
	}

	function saveData(data, page_id, type) {
		return new Promise(function(resolve) {
			$.ajax({
				type: "POST",
				url: "index.php?pb3ajax=1&task=saveContent",
				data: {
					pb_data: data,
					id: page_id,
					type: type
				}
			}).complete(function(result) {
				resolve(result);
			});
		});
	}






});
