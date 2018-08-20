<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
		<style>
			.form-group {
				margin-top:20px;
			}
			textarea {
				font-family:courier;
			}
			#staff {
				display: table;
				margin:0 auto;
			}
		</style>
	</head>
	<body>
		<div class='container'>
			<div class='form-group'>
				<label for='tab-input'><h3>Paste Tab Here</h3></label>
				<textarea class='form-control' id='tab-input' rows='12'></textarea>
			</div>
			<button class="btn btn-primary" id='submit-button'>Submit</button>
			<button class="btn btn-primary" id='sample'>Sample Input</button>
			<button class="btn btn-primary" id='reset'>Reset</button>
			<div id='staff'></div>
		</div>
		<script type="text/javascript" src="sample_tab.js"></script>
		<script type="text/javascript" src="vexflow-min.js"></script>
		<script
		  src="https://code.jquery.com/jquery-3.3.1.min.js"
		  integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8="
		  crossorigin="anonymous"></script>
		<script>
			VF = Vex.Flow;

			$('#submit-button').click(function() {
				$('#staff').html('');
				var input = $('#tab-input').val();


				$.post('tab_ajax.php', { input : input }, function(data) {
					// Create an SVG renderer and attach it to the DIV element named "boo".
					var div = document.getElementById("staff");
					var renderer = new VF.Renderer(div, VF.Renderer.Backends.SVG);

					// Size our svg:
					var height = 200 * data.length;
					renderer.resize(1020, height);

					// And get a drawing context:
					var context = renderer.getContext();

					var start = 10;
					var width = 1000;

					$.each(data, function(i3,v3) {		
						var notes = [];
						$.each(v3, function(index,value) {
							var note_temp = [];
							var temp;
							var accidentals = [];
							if (value.constructor === Array) {
								$.each(value, function(i2, v2) {
									temp = v2.slice(0, -1) + "/" + v2.slice(-1);
									note_temp.push(temp);
									if (temp.charAt(1) == '#' || temp.charAt(1) == 'b')  {
										accidentals.push({'symbol':temp.charAt(1), 'note':i2});
									}
								});
							} else {
								temp = value.slice(0, -1) + "/" + value.slice(-1);
								note_temp.push(temp);
								if (temp.charAt(1) == '#' || temp.charAt(1) == 'b')  {
									accidentals.push({'symbol':temp.charAt(1), 'note':0});
								}
							}

							var stavenote = new VF.StaveNote({clef: "treble", keys: note_temp, duration: "q" });

							$.each(accidentals, function(i2, v2) {
								stavenote.addAccidental(v2.note, new VF.Accidental(v2.symbol));
							});
							notes.push(stavenote);
						});
		
						// Create a stave
						var stave = new VF.Stave(start, i3 * 150 + 40, width);

						// Add a clef
						stave.addClef("treble");

						// Connect it to the rendering context and draw!
						stave.setContext(context).draw();
					
						VF.Formatter.FormatAndDraw(context, stave, notes);
					});	
				}, 'json');
			});		
			$('#sample').click(function() {
				$('#tab-input').val(sample_tab);
			});
			$('#reset').click(function() {
				$('#tab-input').val('');
				$('#staff').html('');
			});

		</script>
	</body>
</html>
