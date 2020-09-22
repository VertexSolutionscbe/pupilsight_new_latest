//_toastr("Welcome, you have 2 new orders","top-right","success",false);
			/** SALES CHART
			******************************************* **/
			loadScript(plugin_path + "chart.flot/jquery.flot.min.js", function(){
				loadScript(plugin_path + "chart.flot/jquery.flot.resize.min.js", function(){
					loadScript(plugin_path + "chart.flot/jquery.flot.time.min.js", function(){
						loadScript(plugin_path + "chart.flot/jquery.flot.fillbetween.min.js", function(){
							loadScript(plugin_path + "chart.flot/jquery.flot.orderBars.min.js", function(){
								loadScript(plugin_path + "chart.flot/jquery.flot.pie.min.js", function(){
									loadScript(plugin_path + "chart.flot/jquery.flot.tooltip.min.js", function(){

										if (jQuery("#flot-sales").length > 0) {

											/* DEFAULTS FLOT COLORS */
											var $color_border_color = "#eaeaea",		/* light gray 	*/
												$color_second 		= "#6595b4";		/* blue      	*/


											var d = [
												[1196463600000, 0], [1196550000000, 0], [1196636400000, 0], [1196722800000, 77], [1196809200000, 3636], [1196895600000, 3575], [1196982000000, 2736], [1197068400000, 1086], [1197154800000, 676], [1197241200000, 1205], [1197327600000, 906], [1197414000000, 710], [1197500400000, 639], [1197586800000, 540], [1197673200000, 435], [1197759600000, 301], [1197846000000, 575], [1197932400000, 481], [1198018800000, 591], [1198105200000, 608], [1198191600000, 459], [1198278000000, 234], [1198364400000, 4568], [1198450800000, 686], [1198537200000, 4122], [1198623600000, 449], [1198710000000, 468], [1198796400000, 392], [1198882800000, 282], [1198969200000, 208], [1199055600000, 229], [1199142000000, 177], [1199228400000, 374], [1199314800000, 436], [1199401200000, 404], [1199487600000, 544], [1199574000000, 500], [1199660400000, 476], [1199746800000, 462], [1199833200000, 500], [1199919600000, 700], [1200006000000, 750], [1200092400000, 600], [1200178800000, 500], [1200265200000, 900], [1200351600000, 930], [1200438000000, 1200], [1200524400000, 980], [1200610800000, 950], [1200697200000, 900], [1200783600000, 1000], [1200870000000, 1050], [1200956400000, 1150], [1201042800000, 1100], [1201129200000, 1200], [1201215600000, 1300], [1201302000000, 1700], [1201388400000, 1450], [1201474800000, 1500], [1201561200000, 1510], [1201647600000, 1510], [1201734000000, 1510], [1201820400000, 1700], [1201906800000, 1800], [1201993200000, 1900], [1202079600000, 2000], [1202166000000, 2100], [1202252400000, 2200], [1202338800000, 2300], [1202425200000, 2400], [1202511600000, 2550], [1202598000000, 2600], [1202684400000, 2500], [1202770800000, 2700], [1202857200000, 2750], [1202943600000, 2800], [1203030000000, 3245], [1203116400000, 3345], [1203202800000, 3000], [1203289200000, 3200], [1203375600000, 3300], [1203462000000, 3400], [1203548400000, 3600], [1203634800000, 3700], [1203721200000, 3800], [1203807600000, 4000], [1203894000000, 4500]];
										
											for (var i = 0; i < d.length; ++i) {
												d[i][0] += 60 * 60 * 1000;
											}
										
											var options = {

												xaxis : {
													mode : "time",
													tickLength : 5
												},

												series : {
													lines : {
														show : true,
														lineWidth : 1,
														fill : true,
														fillColor : {
															colors : [{
																opacity : 0.1
															}, {
																opacity : 0.15
															}]
														}
													},
												   //points: { show: true },
													shadowSize : 0
												},

												selection : {
													mode : "x"
												},

												grid : {
													hoverable : true,
													clickable : true,
													tickColor : $color_border_color,
													borderWidth : 0,
													borderColor : $color_border_color,
												},

												tooltip : true,

												tooltipOpts : {
													content : "Sales: %x <span class='block'>$%y</span>",
													dateFormat : "%y-%0m-%0d",
													defaultTheme : false
												},

												colors : [$color_second],
										
											};
										
											var plot = jQuery.plot(jQuery("#flot-sales"), [d], options);
										}

									});
								});
							});
						});
					});
				});
			});
loadScript(plugin_path + "datatables/js/jquery.dataTables.min.js", function(){
				loadScript(plugin_path + "datatables/dataTables.bootstrap.js", function(){
					loadScript(plugin_path + "select2/js/select2.full.min.js", function(){

						if (jQuery().dataTable) {

							function restoreRow(oTable, nRow) {
								var aData = oTable.fnGetData(nRow);
								var jqTds = $('>td', nRow);

								for (var i = 0, iLen = jqTds.length; i < iLen; i++) {
									oTable.fnUpdate(aData[i], nRow, i, false);
								}

								oTable.fnDraw();
							}

							function editRow(oTable, nRow) {
								var aData = oTable.fnGetData(nRow);
								var jqTds = $('>td', nRow);
								jqTds[0].innerHTML = '<input type="text" class="form-control input-small" value="' + aData[0] + '">';
								jqTds[2].innerHTML = '<a class="edit" href="">Save</a>';
								jqTds[3].innerHTML = '<a class="cancel" href="">Cancel</a>';
							}

							function saveRow(oTable, nRow,id) {
								var id=id;
								alert(id);
								var jqInputs = $('input', nRow);
								oTable.fnUpdate(jqInputs[0].value, nRow, 0, false);
								oTable.fnUpdate('<a class="edit" href="">Edit</a>', nRow, 2, false);
								oTable.fnUpdate('<a class="delete" data-id="'+id+'" href="">Delete</a>', nRow, 3, false);
								oTable.fnDraw();
							}

							function cancelEditRow(oTable, nRow) {
								var jqInputs = $('input', nRow);
								oTable.fnUpdate(jqInputs[0].value, nRow, 0, false);
								oTable.fnUpdate(jqInputs[3].value, nRow, 3, false);
								oTable.fnUpdate('<a class="edit" href="">Edit</a>', nRow, 2, false);
								oTable.fnDraw();
							}

							var table = $('#sample_editable_1');

							var oTable = table.dataTable({
								"lengthMenu": [
									[5, 15, 20, -1],
									[5, 15, 20, "All"] // change per page values here
								],
								// set the initial value
								"pageLength": 10,

								"language": {
									"lengthMenu": " _MENU_ records"
								},
								"columnDefs": [{ // set default column settings
									'orderable': true,
									'targets': [0]
								}, {
									"searchable": true,
									"targets": [0]
								}],
								"order": [
									[0, "asc"]
								] // set first column as a default sort by asc
							});

							var tableWrapper = $("#sample_editable_1_wrapper");

							tableWrapper.find(".dataTables_length select").select2({
								showSearchInput: false //hide search box with special css class
							}); // initialize select2 dropdown

							var nEditing = null;
							var nNew = false;

							$('#sample_editable_1_new').click(function (e) {
								e.preventDefault();
                                var id='';
								if (nNew && nEditing) {
									if (confirm("Previose row not saved. Do you want to save it ?")) {
										saveRow(oTable, nEditing,id); // save
										$(nEditing).find("td:first").html("Untitled");
										nEditing = null;
										nNew = false;

									} else {
										oTable.fnDeleteRow(nEditing); // cancel
										nEditing = null;
										nNew = false;
										
										return;
									}
								}

								var aiNew = oTable.fnAddData(['', '', '', '', '', '']);
								var nRow = oTable.fnGetNodes(aiNew[0]);
								editRow(oTable, nRow);
								nEditing = nRow;
								nNew = true;
							});

							table.on('click', '.delete', function (e) {
								e.preventDefault();

								if (confirm("Are you sure to delete this row ?") == false) {
									return;
								}
								var tex=$(this);
                                var id=$(this).attr('data-id');
                                var type="Del_Cat";
								$.ajax({
								type      : 'POST',
								crossDomain : true,
								url       : 'ajax/ajax_calls.php', 
								data      : {type:type,id:id},
								success   : function(data) {
								if(data>0){ 
								var nRow = tex.parents('tr')[0];
								oTable.fnDeleteRow(nRow);
								}else {
								alert(data);
								}
								}
								});
							});
                            table.on('click', '.delete_pruduct', function (e) {
								e.preventDefault();

								if (confirm("Are you sure to delete this row ?") == false) {
									return;
								}
								var tex=$(this);
                                var id=$(this).attr('data-id');
                                var type=$(this).attr('data-type');
								$.ajax({
								type      : 'POST',
								crossDomain : true,
								url       : 'ajax/ajax_calls.php', 
								data      : {type:type,id:id},
								success   : function(data) {
								if(data>0){ 
								var nRow = tex.parents('tr')[0];
								oTable.fnDeleteRow(nRow);
								}else {
								alert(data);
								}
								}
								});
							});
							table.on('click', '.delete_urs', function (e) {
								e.preventDefault();

								if (confirm("Are you sure to delete this row ?") == false) {
									return;
								}
								var tex=$(this);
                                var id=$(this).attr('data-id');
                                var type=$(this).attr('data-type');
								$.ajax({
								type      : 'POST',
								crossDomain : true,
								url       : 'ajax/ajax_calls.php', 
								data      : {type:type,id:id},
								success   : function(data) {
								if(data>0){ 
								var nRow = tex.parents('tr')[0];
								oTable.fnDeleteRow(nRow);
								}else {
								alert(data);
								}
								}
								});
							});
							table.on('click', '.cancel', function (e) {
								e.preventDefault();

								if (nNew) {
									oTable.fnDeleteRow(nEditing);
									nNew = false;
								} else {
									restoreRow(oTable, nEditing);
									nEditing = null;
								}
							});

							table.on('click', '.edit', function (e) {
								e.preventDefault();
                      
								/* Get the row as a parent of the link that was clicked on */
								var nRow = $(this).parents('tr')[0];
								var id=$(this).attr('data-id');
								if (nEditing !== null && nEditing != nRow) {
									/* Currently editing - but not this row - restore the old before continuing to edit mode */
									restoreRow(oTable, nEditing);
									editRow(oTable, nRow);
									nEditing = nRow;
								} else if (nEditing == nRow && this.innerHTML == "Save") {
									/* Editing this row and want to save it */
									saveRow(oTable, nEditing,id);
									nEditing = null;
									//alert("Updated! Do not forget to do some ajax to sync with backend :)");
								} else {
									/* No edit in progress - let's start one */
									editRow(oTable, nRow);
									nEditing = nRow;
								}
							});

						}

					});
				});
			});
