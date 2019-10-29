
<link rel="stylesheet"
      href="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css"
      integrity="sha384-HSMxcRTRxnN+Bdg0JdbxYKrThecOKuH5zCYotlSAcp1+c8xmyTe9GYg1l9a69psu"
      crossorigin="anonymous">
<script
	src="http://code.jquery.com/jquery-3.3.1.min.js"
	integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8="
	crossorigin="anonymous"></script>


<script>
	let error;
	function error_message(){
		$('#response_status').html("<div class='alert alert-danger'><h4>Fix the errors:</h4><ul>"+error+"</ul></div>");
	}

	function fillTable(){
		$('#response_status').html('');
		$.ajax({
			type: 'POST',
			url: 'db.php',
			data: {'action': 'fill'},
			beforeSend: function () {
				//$('#response_status').html('<div class="alert alert-info">Try to fill the table...</div>');
			},
			success: function (data) {
				$('#locale_table').html(data);
				if (error) {
                    error_message();
				}
			}
		});
	}


	function deleteLanguage(e){
        let deletedLang = $(this);
        let deletedLangHref = $(deletedLang).attr('href'); // ?del=123
        let deletedLangId = deletedLangHref.substr(deletedLangHref.indexOf('=') +1); // 123
		e.preventDefault();

        $.ajax({
            type: 'GET',
            url: 'db.php',
            data: {'del': deletedLangId},
            beforeSend: function () {
                //$(deletedLang).after('<div class="response_status">deleting...</div>');
            },
            success: function (data) {
                //$('.response_status').delete();
	            fillTable();
            }
        });
    }


    function editLanguage(e) {
		e.preventDefault();

        if ( $('#locale_table').find('.save').length ) {
        	return false;
        } else {
        	let editedLang = $(this);
        	let parentRow = editedLang.closest('tr');
        	let editedLangHref = $(this).attr('href');  // ?edit=123
        	let editedLangId = editedLangHref.substr(editedLangHref.indexOf('=') +1); // 123

            $.ajax({
                type: 'GET',
                url: 'db.php',
                data: {'edit': editedLangId},
                beforeSend: function () {
                },
                success: function(data) {
                	$(parentRow).html(data);
                }
            });
        }
    }


	function saveLanguage(){
		let savedLanguage = $(this);
		let savedLanguageId = savedLanguage.data('id');
		let parent_row = savedLanguage.closest('tr');
		let lang = $('td:first-child > input' ,parent_row).val();
		let lang_shortcut = $('td:nth-child(2) > input' ,parent_row).val();
		let lang_id = $('td:nth-child(3) > input' ,parent_row).val();

		$.ajax({
			type: 'GET',
			url: 'db.php',
			data: {
				'save': 'save',
				'id': savedLanguageId,
				'lang': lang,
				'lang_id': lang_id,
                'lang_shortcut': lang_shortcut
			},
			beforeSend: function(){
				//$('.response_status > ul', saved).html('Waiting...');
			},
			success: function(error){
				if(error.trim().length == 0){
					fillTable();
					$('#response_status').html('<div class="alert alert-success"><h4>Successfully changed</h4></div>');
				} else {
					$('#response_status').html('<div class="alert alert-danger">'+error+'</div>');
				}
			},
		});
	}


    function addNewLanguage(e){
		e.preventDefault();

	    $('#response_status').html('');

		let form = $(this).closest('form');
		
		$.ajax({
            type: $(form).attr('method'),
            url: 'db.php',
            data: $(form).serializeArray(),
            beforeSend: function () {
	            //$('#response_status').html('<div class="alert alert-info">Waiting...</div>');
            },
            success: function (error) {
                if (error.trim() == 0) {
                	$(form).trigger('reset');
                	fillTable();
	                $('#response_status').html('<div class="alert alert-success"><h4>Successfully added</h4></div>');
                } else {
	                $('#response_status').html('<div class="alert alert-danger">'+error+'</div>');
                }
            }
        });
    }



	$(function () {
		// initializing
		fillTable();
		// delete the language
        $('#locale_table').on('click', 'table td .del', deleteLanguage);
		// edit the language
		$('#locale_table').on('click', 'table td .edit', editLanguage);
		// saving changes
		$('#locale_table').on('click', 'table td .save', saveLanguage);
		$('#locale_table').on('click', 'table td .cancel', fillTable);
        // add new language
		$('#locale_table').on('click', 'table td .addnew', addNewLanguage);
	});
</script>



<?php

ini_set('display_errors', 'on');
error_reporting(E_ALL);

function debug($data)
{
	echo '<pre>' .print_r($data,1). '</pre>';
}

function showAlert($text, $type)
{
	print_r("<div class='alert alert-".$type."' role='alert'>".$text."</div>");
}

function showDieMsg($text, $error)
{
	die("<div class='alert alert-danger' role='alert'>".$text.":<br><strong>".$error."</strong></div>");
}



/**
 * Connect to DB:
 */
require_once './db.php';

/**
 * Show existing locales:
 */
?>

<div class="container">
	<div class="row">
		<div class="col-xs-12">
            <div id="response_status"></div>

			<h3>Existing Languages</h3>
			<div id="locale_table"></div>
		</div>
	</div>
</div>
