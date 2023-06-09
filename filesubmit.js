$('document').ready(function() {
//Upload xml file
	$("#file-form").submit(function(evt){ //button from the managedb
		evt.preventDefault();

		var theFile = $('#file').get(0).files[0];
		var data = new FormData($(this)[0]);
		var check = $('#file').get(0).files.length;

		if(check == 0) {
			return;
		}

		$.ajax({
			type : 'POST',
			url  : 'upload.php',
			data : data,
			processData: false,
			contentType: false,
			cache:false,
			success : function(response){
				if(response=="Ok"){
					console.log(response);
					alert("File uploaded successfully.");
					document.getElementById("bt-file").innerHTML = "Upload file";
					document.getElementById("result").innerHTML = "Loaded file: " + theFile.name;
					$('#file').val("");
					parsing();
				}
				else {
					alert(response);
					document.getElementById("bt-file").innerHTML = "Upload file";
					document.getElementById("result").innerHTML = "";
					$('#file').val("");
					//console.log($('#file').get(0).files);
				}
			},
			error: function(err) {
				console.log(err);
				alert("Failed to load file.");
				document.getElementById("bt-file").innerHTML = "Upload file";
				document.getElementById("result").innerHTML = "";
				$('#file').val("");
				//console.log($('#file').get(0).files);
			}
		});
	});
});

function ChangeText() {
	if ($('#file').get(0).files.length == 0) {
		return;
	}
	document.getElementById("bt-file").innerHTML = $('#file').get(0).files[0].name;
	document.getElementById('bt-hid').click();
}

function parsing() {
	$("#loader").html('<img src="images/ajax-loader.gif" /> &nbsp; Load data into database ...');

	$.ajax({
		type : 'POST',
		url  : 'parser.php',
		success : function(response){
			if(response=="Ok"){
				console.log(response);
				$("#loader").empty();
				alert('Data has been successfully loaded into the Database.');
			}
			else {
				alert("A Load Error Occurred");
				$("#loader").empty();
			}
		},
		error: function(err) {
			console.log(err);
			$("#loader").empty();
			alert("Failed to load data into Database.");
		}
	});

}
//Sorting tables from the database
function deleteDB() {
	var r = confirm("You definitely want to delete the existing data;");
	if (r == true) {
		console.log("Deleted");

		$.ajax({
			type: "POST",
			url: "delete_db.php",
			success: function(response) {
				alert(response);
			},
			error: function(xhr,textStatus,errorThrown) {
				alert('Delete failed');
			}
		});

	} else {
		console. log("Not Deleted");
		return;
	}
}