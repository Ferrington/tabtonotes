<?php

class Tab_translator {
	
	private $notes;
	private $note_dict = array(
					'C'  =>  0,
					'C#' =>  1,
					'D'  =>  2,
					'D#' =>  3,
					'E'  =>  4,
					'F'  =>  5,
					'F#' =>  6,
					'G'  =>  7,
					'G#' =>  8,
					'A'  =>  9,
					'A#' => 10,
					'B'  => 11
				);

	public function __construct($input_string)
	{
		$this->notes = $this->convert_staves($input_string);
	}

	public function get_notes()
	{
		return $this->notes;
	}

	private function convert_staves($input) 
	{
		$tab_arr = array();
		$staff = "";
		$count = 0;
		$separator = "\r\n";
		$line = strtok($input, $separator);

		while ($line !== false) {
			if (substr_count($line,'-') > 4) {
				$staff .= $line."\n";
				$count++;
			}
			if ($count == 6) {
				$tab_arr[] = $this->convert_tab($staff);
				$count = 0;
				$staff = "";
			}

			$line = strtok($separator);
		}

		return $tab_arr;
	}

	private function convert_tab($input)
	{
		$notes = array();
		$modified_notes = array();

		//up an octave so notes sit properly on treble staff
		$dict = array(
					'E5',
					'B4',
					'G4',
					'D4',
					'A3',
					'E3'
				);
		$lines = preg_split('/\r\n|\n|\r/', $input);
		
		foreach ($lines as $index => $line) {
			$offset = 0;

			preg_match_all('!\d+!', $line, $matches);
			foreach ($matches[0] as $match) {
				$pos = strpos($line, $match, $offset);
				$note = $this->note_alter($dict[$index],$match);
				$notes[] = array('position' => $pos, 'note' => $note, 'length' => strlen($match));
				$offset = $pos + 1;
			}
		}

		usort($notes, function($a, $b) {
			    return $a['position'] - $b['position'];
		});

		$skip_arr = array();
		foreach ($notes as $index => $note) {
			if (in_array($index, $skip_arr)) continue;
			$i = 1;
			$temp_arr = array();
			while(true) {
				if (isset($notes[$index + $i]['position']) and $notes[$index + $i]['position'] < $note['position']) {
					$strlen = $notes[$index + $i]['length'];
				} else {
					$strlen = $note['length'];
				}

				if (isset($notes[$index + $i]['position']) and ($notes[$index + $i]['position'] - $note['position'] == 0
					or (abs($notes[$index + $i]['position'] - $note['position']) == 1 and $strlen == 2))) {
					$temp_arr[] = $notes[$index + $i]['note'];
					$skip_arr[] = $index + $i;
					$i++;
				} else {
					break;
				}
			}
			if ($temp_arr) {
				$modified_notes[] = $this->note_sort(array_merge(array($note['note']), $temp_arr));
			} else {
				$modified_notes[] = $note['note'];
			}
		}

		return $modified_notes;
	}

	/*
	 * $notes - array
	 */
	private function note_sort($notes)
	{
		$sorted = array();

		foreach ($notes as $note) {
			$octave = substr($note, -1);
			$note_ = substr($note, 0, -1);
			$note_num = $this->note_dict[substr($note,0,1)]; 		
			if (substr($note_,1,1) == 'b') {
				$note_num--;
			} elseif (substr($note_,1,1) == '#') {
				$note_num++;
			}
			$note_num += $octave * 12;

			$sorted[$note_num] = $note;
		}
	
		ksort($sorted);
		return array_values($sorted);
	}

	/*
	 * $note - letter, symbol (optional), number (e.g. 'Bb4', C#5, D3)
	 * $half_steps - positive or negative half steps to add
	 */
	private function note_alter($note, $half_steps)
	{
		$octave = substr($note, -1);
		$note = substr($note, 0, -1);

		$note_num = $this->note_dict[$note[0]];
		if (substr($note,1,1) == 'b') {
			$note_num--;
		} elseif (substr($note,1,1) == '#') {
			$note_num++;
		}

		$result_num = $note_num + $half_steps;

		$mod = $result_num % 12;

		if ($mod < 0) {
			$mod += 12;
		}
		$result_note = array_search($mod, $this->note_dict);	
		$result_octave = floor($result_num / 12) + $octave;

		return $result_note.$result_octave;
	}
}
/*
$test_string = "eb|-------------------------------------------------------------------|
Bb|-------------------------------------------------------------------|
Gb|-------------------------------------------------------------------|
Db|-11--11.-11-12-12/14--14-12-11-11----------------------------------|
Ab|-0---0---0--0--0------0--0--0--0---14--14--11-12-12/14--14-12-11---|
Eb|-----------------------------------0---0---0--0--0------0--0--0----|
                                   
                                  
eb|--------------------------------------------------------------|
Bb|--------------------------------------------------------------|
Gb|-11--11--11-9-9----14p12p11-----------------------------------|
Db|-0---0---0--0-0--0----------12p11p9-11--11-11-9.---7-9b(1)r-7-|
Ab|-0---0---0--0-0--0------------------0---0--0--0---(0)-------0-|
Eb|--------------------------------------------------------------|";

$tt = new Tab_translator($test_string);

print_r($tt->get_notes());
 */
