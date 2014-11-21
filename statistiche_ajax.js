/*----------------------------------------------------------------------------------------------
 *
 * Author: Valerio Giacomelli
 * Company: Syncronika
 * Email valerio.giacomelli at syncronika.it
 *
 -----------------------------------------------------------------------------------------------*/
//COSTANTI
var popup;
var popup_logout;

var DIV_POPUP = "DIV_POPUP_CHART";
var DIV_POPUP_STATISTICHE = "DIV_POPUP_STATISTICHE";
var DIV_POPUP_LOGOUT = 'DIV_POPUP_LOGOUT';

var MAIN_GRID_ID = 'STATISTICHE';
var TXT_FILTERTOKENS_ID = "TXT_FilterTokens_" + MAIN_GRID_ID;

var MAIN_GRID_ID_FAMIGLIE = 'STATISTICHE_FAMIGLIA';
var TXT_FILTERTOKENS_ID_FAMIGLIE = "TXT_FilterTokens_" + MAIN_GRID_ID_FAMIGLIE;

var FADEIN_TIME = 300;

//------------------------------------------------------------------------------------------------------------------------------------------------

function create_popup() {
	var popup = $('<div>');
	popup.attr('id', DIV_POPUP);
	popup.dialog({
		title: 'Statistiche',
		autoOpen: false,
		closeText: 'Chiudi',
		closeOnEscape: true,
		//hide: 'fade',
		//show: 'fade',
		width: 950,
		//height: 500,
		zIndex: 102,
		resizable: false,
		draggable: true,
		position: 'top',
		modal: true, //others elements disable
		close: function (event, ui) {
			$(event.target).remove();
		},
		open: function (event, ui) {}
	});

	return popup;
}

function create_popup_statistiche() {
	var popup = $('<div>');
	popup.attr('id', DIV_POPUP_STATISTICHE);
	popup.dialog({
		title: 'Statistiche',
		autoOpen: false,
		closeText: 'Chiudi',
		closeOnEscape: true,
		//hide: 'fade',
		//show: 'fade',
		width: 1000,
		//height: 500,
		zIndex: 102,
		resizable: false,
		draggable: true,
		position: 'top',
		modal: true, //others elements disable
		close: function (event, ui) {
			$(event.target).remove();
		},
		open: function (event, ui) {}
	});

	return popup;
}

//------------------------------------------------------------------------------------------------------------------------------------------------

function CLBK_get_statistiche(id_dest_div, Grid_PageIndex, Grid_SortField, Grid_SortDirection, Grid_FilterTokens) {
	
	ShowLoader(150, 100);

	var Grid_FilterTokensSetted = (Grid_FilterTokens == '' ? $.cookie(TXT_FILTERTOKENS_ID) : Grid_FilterTokens);
	$('#' + TXT_FILTERTOKENS_ID).val(Grid_FilterTokensSetted);

	$.ajax({
		type: "POST",
		url: ABSOLUTE_SITE_URL + '/statistiche/show_ajax/' + Grid_PageIndex,
		data: ({
			GRID_PAGE_INDEX: Grid_PageIndex,
			GRID_SHORT_FIELD: Grid_SortField,
			GRID_SORT_DIRECTION: Grid_SortDirection,
			TOKEN_FILTER: Grid_FilterTokensSetted, //$('#'+TXT_FILTERTOKENS_ID).val(),
			TOKEN_FILTER_ID: TXT_FILTERTOKENS_ID
		}),
		dataType: "json",
		success: function (res) {
			if (res.esito > 0) {
				$(id_dest_div).hide();
				$(id_dest_div).html(res.html);
				$(id_dest_div).fadeIn(FADEIN_TIME);
	
				$("tr:nth-child(odd)").addClass("odd");
			} else {
				if (res.logged > 0) {
					popup_errore = create_popup_error();
					
					var html_errore = '<div style="margin:10px;">' + res.messaggio + '</div>';
					popup_errore.html(html_errore);
					popup_errore.dialog('open');
				} else {
					popup_logout = create_popup_logout();
		
					var html_errore = '<div style="margin:10px;">' + res.messaggio + '</div>';
					popup_logout.html(html_errore);
					popup_logout.dialog('open');
				}
			}
			RemoveLoader();
		},
		error: function () {
				RemoveLoader();
			
				popup_errore = create_popup_error();
					
				var html_errore = '<div style="margin:10px;">Problema chiamata ajax</div>';
				popup_errore.html(html_errore);
				popup_errore.dialog('open');
		}
	});


	return false;
}

function CLBK_get_statistiche_famiglie(id_dest_div, Grid_PageIndex, Grid_SortField, Grid_SortDirection, Grid_FilterTokens) {
	
	ShowLoader(150, 100);

	var Grid_FilterTokensSetted = (Grid_FilterTokens == '' ? $.cookie(TXT_FILTERTOKENS_ID_FAMIGLIE) : Grid_FilterTokens);
	$('#' + TXT_FILTERTOKENS_ID_FAMIGLIE).val(Grid_FilterTokensSetted);

	$.ajax({
		type: "POST",
		url: ABSOLUTE_SITE_URL + '/statistiche/show_famiglie_ajax/' + Grid_PageIndex,
		data: ({
			GRID_PAGE_INDEX: Grid_PageIndex,
			GRID_SHORT_FIELD: Grid_SortField,
			GRID_SORT_DIRECTION: Grid_SortDirection,
			TOKEN_FILTER: Grid_FilterTokensSetted, //$('#'+TXT_FILTERTOKENS_ID).val(),
			TOKEN_FILTER_ID: TXT_FILTERTOKENS_ID_FAMIGLIE
		}),
		dataType: "json",
		success: function (res) {
			if (res.esito > 0) {
				$(id_dest_div).hide();
				$(id_dest_div).html(res.html);
				$(id_dest_div).fadeIn(FADEIN_TIME);
	
				$("tr:nth-child(odd)").addClass("odd");
			} else {
				if (res.logged > 0) {
					popup_errore = create_popup_error();
					
					var html_errore = '<div style="margin:10px;">' + res.messaggio + '</div>';
					popup_errore.html(html_errore);
					popup_errore.dialog('open');
				} else {
					popup_logout = create_popup_logout();
		
					var html_errore = '<div style="margin:10px;">' + res.messaggio + '</div>';
					popup_logout.html(html_errore);
					popup_logout.dialog('open');
				}
			}
			RemoveLoader();
		},
		error: function () {
				RemoveLoader();
			
				popup_errore = create_popup_error();
					
				var html_errore = '<div style="margin:10px;">Problema chiamata ajax</div>';
				popup_errore.html(html_errore);
				popup_errore.dialog('open');
		}
	});


	return false;
}


//------------------------------------------------------------------------------------------------------------------------------------------------
function CLBK_popup_chart() {

	popup = create_popup();

	ShowLoader(150, 100);

	$.ajax({
		type: "POST",
		url: ABSOLUTE_SITE_URL + '/statistiche/popup_chart_ajax/',
		dataType: "json",
		success: function (res) {
			RemoveLoader();
				popup.html(res.html);
				popup.dialog('open');
		},
		error: function () {
				RemoveLoader();
			
				popup_errore = create_popup_error();
				
				var html_errore = '<div style="margin:10px;">Problema chiamata ajax</div>';
				popup_errore.html(html_errore);
				popup_errore.dialog('open');
		}
	});

	return false;
}


function CLBK_Refresh_AfterFilter(id_dest_div, Grid_PageIndex, Grid_SortField, Grid_SortDirection, Grid_FilterTokens) {
	CLBK_get_statistiche(id_dest_div, Grid_PageIndex, Grid_SortField, Grid_SortDirection, Grid_FilterTokens);
}

function CLBK_Refresh_AfterFilter_famiglie(id_dest_div, Grid_PageIndex, Grid_SortField, Grid_SortDirection, Grid_FilterTokens) {
	CLBK_get_statistiche_famiglie(id_dest_div, Grid_PageIndex, Grid_SortField, Grid_SortDirection, Grid_FilterTokens);
}

//------------------------------------------------------------------------------------------------------------------------------------------------
//REFRESH

function CLBK_refresh_statistiche() {
	CLBK_get_statistiche('#ajax', 0, '', '', $('#' + TXT_FILTERTOKENS_ID).val());
	CLBK_get_statistiche_famiglie('#statistiche_famiglia_ajax', 0, '', '', $('#' + TXT_FILTERTOKENS_ID_FAMIGLIE).val());
}

//------------------------------------------------------------------------------------------------------------------------------------------------
//CLEAN FILTERS

function CLBK_clean_filters() {
	RemoveTokensCookie($('#' + TXT_FILTERTOKENS_ID));
	RemoveTokensCookie($('#' + TXT_FILTERTOKENS_ID_FAMIGLIE));
	CLBK_get_statistiche('#ajax', 0, '', '', null);
	CLBK_get_statistiche_famiglie('#statistiche_famiglia_ajax', 0, '', '', null);
}
//------------------------------------------------------------------------------------------------------------------------------------------------



//------------------------------------------------------------------------------------------------------------------------------------------------


	/*******************************************************
					FUNZIONI GRAFICI
	*******************************************************/


function CBK_chart_commesse(resi){
	
	series = resi;
			
	$(function () {
								
		$('#container').highcharts({
				title: {
					text: 'Prodotti Resi per mese',
					x: -20 //center
				},
				xAxis: {
					categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
						'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
				},
				yAxis: {
					title: {
						text: 'Numero prodotti resi'
					},
					plotLines: [{
						value: 0,
						width: 1,
						color: '#808080'
					}]
				},
				tooltip: {
					valueSuffix: ''
				},
				legend: {
					layout: 'vertical',
					align: 'right',
					verticalAlign: 'middle',
					borderWidth: 0
				},
				series: [{
					name: 'Resi mensili',
					data: series
				}]
				
		});
						
	});	
			
}

function CLBK_get_statistiche_per_anno(){
	ShowLoader(150, 100);
	$.ajax({
		type: "POST",
		url: ABSOLUTE_SITE_URL + '/statistiche/get_commesse_for_json/',
		data: ({
			anno_selected: $('#CMB_anno').val()
		}),
		dataType: "json",
		success: function (res) {
			var resi = new Array();
			for(index in res){
				resi.push(res[index]);
			}
			CBK_chart_commesse(resi);
			RemoveLoader();
		},
	})
	
}

function CLBK_export_data() {

	ShowLoader(150, 100);

	$.ajax({
		type: "POST",
		url: ABSOLUTE_SITE_URL + '/statistiche/export_data/',
		data: ({
			anno: $('#CMB_anno').val(),
			mese: $('#CMB_mese').val()
		}),
		dataType: "json",
		success: function (risultato) {
			RemoveLoader();
			if (risultato.esito != null) {
				document.location = ABSOLUTE_SITE_URL + '/statistiche/export_data/';
			}
		},
		error: function () {
			RemoveLoader();
			popup_errore = create_popup_error();
			popup_errore.dialog('open');
			var html_errore = '<div style="margin:10px;">Ajax error</div>';
			popup_errore.html(html_errore);
		},
		statusCode: {
			401: function(jqXHR, textStatus, errorThrown){
				var data = eval('('+jqXHR.responseText+')');
				var html_errore = '<div style="margin:10px;">'+data.textStatus+'</div>';
				
				popup_errore = create_popup_error();
				popup_errore.html(html_errore);
				popup_errore.dialog('option', 'buttons', {});
				popup_errore.on("dialogclose", function(event, ui){
					window.location = ABSOLUTE_SITE_URL + 'auth/login';
				});
				popup_errore.dialog('open');
			}
		}
	});

	return false;
}

function CLBK_export_data_singolo_prodotto(prod_id) {

	ShowLoader(150, 100);

	$.ajax({
		type: "POST",
		url: ABSOLUTE_SITE_URL + '/statistiche/export_data_singolo_prodotto/',
		data: ({
			anno: $('#CMB_anno').val(),
			mese: $('#CMB_mese').val(), 
			prod_id: prod_id
		}),
		dataType: "json",
		success: function (risultato) {
			RemoveLoader();
			if (risultato.esito != null) {
				document.location = ABSOLUTE_SITE_URL + '/statistiche/export_data_singolo_prodotto/';
			}
		},
		error: function () {
			RemoveLoader();
			popup_errore = create_popup_error();
			popup_errore.dialog('open');
			var html_errore = '<div style="margin:10px;">Ajax error</div>';
			popup_errore.html(html_errore);
		},
		statusCode: {
			401: function(jqXHR, textStatus, errorThrown){
				var data = eval('('+jqXHR.responseText+')');
				var html_errore = '<div style="margin:10px;">'+data.textStatus+'</div>';
				
				popup_errore = create_popup_error();
				popup_errore.html(html_errore);
				popup_errore.dialog('option', 'buttons', {});
				popup_errore.on("dialogclose", function(event, ui){
					window.location = ABSOLUTE_SITE_URL + 'auth/login';
				});
				popup_errore.dialog('open');
			}
		}
	});

	return false;
}


function CLBK_export_data_famiglia_prodotto(fam_id) {

	ShowLoader(150, 100);

	$.ajax({
		type: "POST",
		url: ABSOLUTE_SITE_URL + '/statistiche/export_data_famiglia_prodotto/',
		data: ({
			anno: $('#CMB_anno').val(),
			mese: $('#CMB_mese').val(), 
			fam_id: fam_id
		}),
		dataType: "json",
		success: function (risultato) {
			RemoveLoader();
			if (risultato.esito != null) {
				document.location = ABSOLUTE_SITE_URL + '/statistiche/export_data_famiglia_prodotto/';
			}
		},
		error: function () {
			RemoveLoader();
			popup_errore = create_popup_error();
			popup_errore.dialog('open');
			var html_errore = '<div style="margin:10px;">Ajax error</div>';
			popup_errore.html(html_errore);
		},
		statusCode: {
			401: function(jqXHR, textStatus, errorThrown){
				var data = eval('('+jqXHR.responseText+')');
				var html_errore = '<div style="margin:10px;">'+data.textStatus+'</div>';
				
				popup_errore = create_popup_error();
				popup_errore.html(html_errore);
				popup_errore.dialog('option', 'buttons', {});
				popup_errore.on("dialogclose", function(event, ui){
					window.location = ABSOLUTE_SITE_URL + 'auth/login';
				});
				popup_errore.dialog('open');
			}
		}
	});

	return false;
}



function CBK_popup_statistiche(id_prodotto) {
	
	ShowLoader(150, 100);

	popup = create_popup_statistiche();

	$.ajax({
		type: "POST",
		url: ABSOLUTE_SITE_URL + '/statistiche/popup_statistiche_ajax/',
		data: ({
			prod_id: id_prodotto
		}),
		dataType: "json",
		success: function (res) {
				popup.html(res.html);
				$('#tabs').tabs();
				popup.dialog('open');
				RemoveLoader();				
		},
		error: function () {
				RemoveLoader();
			
				popup_errore = create_popup_error();
				
				var html_errore = '<div style="margin:10px;">Problema chiamata ajax</div>';
				popup_errore.html(html_errore);
				popup_errore.dialog('open');
		}
	});

	return false;
}

function CBK_popup_statistiche_famiglia(id_famiglia) {
	
	ShowLoader(150, 100);

	popup = create_popup_statistiche();

	$.ajax({
		type: "POST",
		url: ABSOLUTE_SITE_URL + '/statistiche/popup_statistiche_famiglia_ajax/',
		data: ({
			fam_id: id_famiglia
		}),
		dataType: "json",
		success: function (res) {
				popup.html(res.html);
				$('#tabs').tabs();
				popup.dialog('open');
				RemoveLoader();				
		},
		error: function () {
				RemoveLoader();
			
				popup_errore = create_popup_error();
				
				var html_errore = '<div style="margin:10px;">Problema chiamata ajax</div>';
				popup_errore.html(html_errore);
				popup_errore.dialog('open');
		}
	});

	return false;
}

	
	/*******************************************************
				GRAFICO RESI PER PRODOTTO
	*******************************************************/

function CBK_chart_statistiche(prod_id, prod_nome, resi, categories){
		
	if(resi  == null && categories == null){
		ShowLoader(150, 100);
		$.ajax({
			type: "POST",
			url: ABSOLUTE_SITE_URL + '/statistiche/get_statistiche_for_json/',
			data: ({
				prod_id: prod_id,
				mese: $('#CMB_mese').val(),
				anno: $('#CMB_anno').val()
			}),
			dataType: "json",
			success: function (res) {
				
				var commesse = new Array();
				for(index in res.series){
					commesse.push(res.series[index]);
				}
				
				var ascisse = new Array();
				for(index in res.categories){
					ascisse.push(res.categories[index]);
				}
				
				get_chart_resi(prod_id, prod_nome, commesse, ascisse);
				
				RemoveLoader();
				
			},
		})
	
	}else{		
		get_chart_resi(prod_id, prod_nome, resi, categories)			
	}			
}

function get_chart_resi(prod_id, prod_nome, commesse, ascisse){
	
	var series = commesse;
	
	var categories = ascisse;
	
	$(function () {
								
		$('#resi_container').highcharts({
				title: {
					text: prod_nome+' - Resi per mese',
					x: -20 //center
				},
				xAxis: {
					categories: categories
				},
				yAxis: {
					title: {
						text: 'Numero prodotti resi'
					},
					plotLines: [{
						value: 0,
						width: 1,
						color: '#808080'
					}]
				},
				tooltip: {
					valueSuffix: ''
				},
				legend: {
					layout: 'vertical',
					align: 'right',
					verticalAlign: 'middle',
					borderWidth: 0
				},
				series: [{
					name: 'Resi mensili',
					data: series
				}]
				
		});
						
	});	
	
}




	/*******************************************************
				GRAFICO DIFETTOSITA' PER PRODOTTO
	*******************************************************/


function CBK_chart_rientri(prod_id, prod_nome){
		
	$.ajax({
		type: "POST",
		url: ABSOLUTE_SITE_URL + '/statistiche/get_rientri_for_json/',
		data: ({
			prod_id: prod_id,
			mese_fine: $('#CMB_mese_fine').val(),
			anno_fine: $('#CMB_anno_fine').val(),
		}),
		dataType: "json",
		success: function (res) {
			var commesse = new Array();
			for(index in res){
				commesse.push(res[index]);
			}
			var series = commesse;

			var categories = [];

			for (i = 0; i < 30; i++) {
				categories[i] = (i+1)+'m';
			}

			$(function () {
								
				$('#rientri_container').highcharts({
						title: {
							text: prod_nome+': Rientri mensili dalla messa in produzione',
							x: -20 //center
						},
						xAxis: {
							categories: categories
						},
						yAxis: {
							title: {
								text: 'Numero prodotti rientrati'
							},
							plotLines: [{
								value: 0,
								width: 1,
								color: '#808080'
							}]
						},
						tooltip: {
							valueSuffix: ''
						},
						legend: {
							layout: 'vertical',
							align: 'right',
							verticalAlign: 'middle',
							borderWidth: 0
						},
						series: [{
							name: 'Resi mensili',
							data: series
						}]
						
				});
								
			});	
			
		},
	})
			
}

function CBK_chart_rientri_famiglia(fam_id, fam_codice){
		
	$.ajax({
		type: "POST",
		url: ABSOLUTE_SITE_URL + '/statistiche/get_rientri_famiglia_for_json/',
		data: ({
			fam_id: fam_id,
			fam_codice: fam_codice,
			mese_fine: $('#CMB_mese_fine').val(),
			anno_fine: $('#CMB_anno_fine').val(),
		}),
		dataType: "json",
		success: function (res) {
			var commesse = new Array();
			for(index in res){
				commesse.push(res[index]);
			}
			var series = commesse;

			var categories = [];

			for (i = 0; i < 30; i++) {
				categories[i] = (i+1)+'m';
			}

			$(function () {
								
				$('#rientri_famiglia_container').highcharts({
						title: {
							text: fam_codice+': Rientri mensili dalla messa in produzione',
							x: -20 //center
						},
						xAxis: {
							categories: categories
						},
						yAxis: {
							title: {
								text: 'Numero prodotti rientrati'
							},
							plotLines: [{
								value: 0,
								width: 1,
								color: '#808080'
							}]
						},
						tooltip: {
							valueSuffix: ''
						},
						legend: {
							layout: 'vertical',
							align: 'right',
							verticalAlign: 'middle',
							borderWidth: 0
						},
						series: [{
							name: 'Resi mensili',
							data: series
						}]
						
				});
								
			});	
			
		},
	})
			
}

function CBK_chart_statistiche_famiglia(fam_id, prod_nome, resi, categories){
		
	if(resi  == null && categories == null){
		ShowLoader(150, 100);
		$.ajax({
			type: "POST",
			url: ABSOLUTE_SITE_URL + '/statistiche/get_statistiche_famiglia_for_json/',
			data: ({
				fam_id: fam_id,
				mese: $('#CMB_mese').val(),
				anno: $('#CMB_anno').val()
			}),
			dataType: "json",
			success: function (res) {
				
				var commesse = new Array();
				for(index in res.series){
					commesse.push(res.series[index]);
				}
				
				var ascisse = new Array();
				for(index in res.categories){
					ascisse.push(res.categories[index]);
				}
				
				get_chart_resi(fam_id, prod_nome, commesse, ascisse);
				
				RemoveLoader();
				
			},
		})
	
	}else{		
		get_chart_resi(fam_id, prod_nome, resi, categories)			
	}			
}