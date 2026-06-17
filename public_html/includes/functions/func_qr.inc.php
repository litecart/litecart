	<?php

	// Minimal QR Code generator (Byte mode, ECC Level L) — reusable functions

	function qr_generate(string $data, int $size = 200): string {
		$encoded = qr_encode($data);
		if (!$encoded) return '';

		$modules = $encoded['modules'];
		$count = count($modules);

		$svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 '. ($count + 8) .' '. ($count + 8) . '" width="'. $size .'" height="'. $size .'">';
		$svg .= '<rect width="100%" height="100%" fill="#fff"/>';

		// Build path data — merge consecutive dark modules per row
		$path = '';
		for ($y = 0; $y < $count; $y++) {
			$x = 0;
			while ($x < $count) {
				if ($modules[$y][$x]) {
					$start = $x;
					while ($x < $count && $modules[$y][$x]) $x++;
					$path .= 'M'. ($start + 4) .','. ($y + 4) .'h'. ($x - $start) .'v1h-'. ($x - $start) .'z';
				} else {
					$x++;
				}
			}
		}

		if ($path) $svg .= '<path d="'. $path .'" fill="#000"/>';

		$svg .= '</svg>';

		return $svg;
	}

	function qr_encode(string $data): array|false {
		$data_bytes = array_values(unpack('C*', $data));
		$data_length = count($data_bytes);

		// Version capacity table (Byte mode, ECC Level L)
		$capacities = [0, 17, 32, 53, 78, 106, 134];

		$version = 0;
		for ($v = 1; $v <= 6; $v++) {
			if ($data_length <= $capacities[$v]) {
				$version = $v;
				break;
			}
		}

		if (!$version) return false; // Data too long

		$module_count = 17 + $version * 4;

		// Total data codewords (ECC Level L)
		$total_codewords = [0, 19, 34, 55, 80, 108, 136];
		$ec_codewords = [0, 7, 10, 15, 20, 26, 36];

		$data_cw_count = $total_codewords[$version];
		$ec_cw_count = $ec_codewords[$version];

		// Build data bitstream
		$bits = '';
		$bits .= '0100'; // Byte mode indicator
		$bits .= str_pad(decbin($data_length), $version <= 1 ? 8 : 16, '0', STR_PAD_LEFT);

		foreach ($data_bytes as $byte) {
			$bits .= str_pad(decbin($byte), 8, '0', STR_PAD_LEFT);
		}

		// Terminator
		$bits .= str_repeat('0', min(4, $data_cw_count * 8 - strlen($bits)));

		// Pad to byte boundary
		while (strlen($bits) % 8 !== 0) {
			$bits .= '0';
		}

		// Pad codewords
		$pad_patterns = ['11101100', '00010001'];
		$pad_index = 0;
		while (strlen($bits) < $data_cw_count * 8) {
			$bits .= $pad_patterns[$pad_index % 2];
			$pad_index++;
		}

		// Convert to codewords
		$codewords = [];
		for ($i = 0; $i < strlen($bits); $i += 8) {
			$codewords[] = bindec(substr($bits, $i, 8));
		}

		// Generate error correction codewords
		$ec = qr_reed_solomon($codewords, $ec_cw_count);
		$all_codewords = array_merge($codewords, $ec);

		// Initialize module grid
		$modules = array_fill(0, $module_count, array_fill(0, $module_count, 0));
		$is_function = array_fill(0, $module_count, array_fill(0, $module_count, false));

		// Place finder patterns
		qr_place_finder($modules, $is_function, 0, 0, $module_count);
		qr_place_finder($modules, $is_function, $module_count - 7, 0, $module_count);
		qr_place_finder($modules, $is_function, 0, $module_count - 7, $module_count);

		// Timing patterns
		for ($i = 8; $i < $module_count - 8; $i++) {
			if (!$is_function[$i][6]) {
				$modules[$i][6] = ($i % 2 === 0) ? 1 : 0;
				$is_function[$i][6] = true;
			}
			if (!$is_function[6][$i]) {
				$modules[6][$i] = ($i % 2 === 0) ? 1 : 0;
				$is_function[6][$i] = true;
			}
		}

		// Dark module
		$modules[$module_count - 8][8] = 1;
		$is_function[$module_count - 8][8] = true;

		// Reserve format info areas
		for ($i = 0; $i < 8; $i++) {
			if (!$is_function[$i][8]) $is_function[$i][8] = true;
			if (!$is_function[8][$i]) $is_function[8][$i] = true;
			if (!$is_function[$module_count - 1 - $i][8]) $is_function[$module_count - 1 - $i][8] = true;
			if (!$is_function[8][$module_count - 1 - $i]) $is_function[8][$module_count - 1 - $i] = true;
		}
		$is_function[8][8] = true;

		// Alignment pattern (version >= 2)
		if ($version >= 2) {
			$align_pos = [0, 6, $module_count - 7];
			$center = $module_count - 7;
			qr_place_alignment($modules, $is_function, $center, $center, $module_count);
		}

		// Place data bits
		$bit_index = 0;
		$all_bits = '';
		foreach ($all_codewords as $cw) {
			$all_bits .= str_pad(decbin($cw), 8, '0', STR_PAD_LEFT);
		}

		$x = $module_count - 1;
		$upward = true;

		while ($x >= 1) {
			if ($x === 6) $x--; // Skip timing column

			for ($i = 0; $i < $module_count; $i++) {
				$y = $upward ? ($module_count - 1 - $i) : $i;

				for ($j = 0; $j < 2; $j++) {
					$col = $x - $j;
					if ($col < 0) continue;
					if ($is_function[$y][$col]) continue;

					$modules[$y][$col] = ($bit_index < strlen($all_bits) && $all_bits[$bit_index] === '1') ? 1 : 0;
					$bit_index++;
				}
			}

			$x -= 2;
			$upward = !$upward;
		}

		// Apply mask pattern 0 (checkerboard)
		for ($y = 0; $y < $module_count; $y++) {
			for ($x = 0; $x < $module_count; $x++) {
				if (!$is_function[$y][$x]) {
					if (($y + $x) % 2 === 0) {
						$modules[$y][$x] ^= 1;
					}
				}
			}
		}

		// Place format info (ECC Level L = 01, Mask 0 = 000)
		$format_bits = [1,0,1,0,1,0,0,0,0,0,1,0,0,1,0]; // Pre-computed: L + mask 0
		$format_positions_a = [[0,8],[1,8],[2,8],[3,8],[4,8],[5,8],[7,8],[8,8],[8,7],[8,5],[8,4],[8,3],[8,2],[8,1],[8,0]];
		$format_positions_b = [[$module_count-1,8],[$module_count-2,8],[$module_count-3,8],[$module_count-4,8],[$module_count-5,8],[$module_count-6,8],[$module_count-7,8],[8,$module_count-8],[8,$module_count-7],[8,$module_count-6],[8,$module_count-5],[8,$module_count-4],[8,$module_count-3],[8,$module_count-2],[8,$module_count-1]];

		for ($i = 0; $i < 15; $i++) {
			$modules[$format_positions_a[$i][0]][$format_positions_a[$i][1]] = $format_bits[$i];
			$modules[$format_positions_b[$i][0]][$format_positions_b[$i][1]] = $format_bits[$i];
		}

		return ['modules' => $modules, 'version' => $version, 'size' => $module_count];
	}

	function qr_place_finder(array &$modules, array &$is_function, int $row, int $col, int $size): void {
		for ($r = -1; $r <= 7; $r++) {
			for ($c = -1; $c <= 7; $c++) {
				$y = $row + $r;
				$x = $col + $c;
				if ($y < 0 || $y >= $size || $x < 0 || $x >= $size) continue;

				$is_function[$y][$x] = true;

				if ($r >= 0 && $r <= 6 && ($c === 0 || $c === 6)) {
					$modules[$y][$x] = 1;
				} elseif ($c >= 0 && $c <= 6 && ($r === 0 || $r === 6)) {
					$modules[$y][$x] = 1;
				} elseif ($r >= 2 && $r <= 4 && $c >= 2 && $c <= 4) {
					$modules[$y][$x] = 1;
				} else {
					$modules[$y][$x] = 0;
				}
			}
		}
	}

	function qr_place_alignment(array &$modules, array &$is_function, int $center_y, int $center_x, int $size): void {
		for ($r = -2; $r <= 2; $r++) {
			for ($c = -2; $c <= 2; $c++) {
				$y = $center_y + $r;
				$x = $center_x + $c;
				if ($y < 0 || $y >= $size || $x < 0 || $x >= $size) continue;
				if ($is_function[$y][$x]) return; // Overlap with finder

				$is_function[$y][$x] = true;
				if (abs($r) === 2 || abs($c) === 2 || ($r === 0 && $c === 0)) {
					$modules[$y][$x] = 1;
				} else {
					$modules[$y][$x] = 0;
				}
			}
		}
	}

	function qr_reed_solomon(array $data, int $ec_count): array {
		$generators = [
			7  => [87,229,146,149,238,102,21],
			10 => [251,67,46,61,118,70,64,94,32,45],
			15 => [29,196,111,163,112,74,10,105,105,139,132,151,32,134,26],
			20 => [173,125,158,2,103,182,118,17,145,201,111,28,165,53,161,21,245,142,13,102],
			26 => [173,125,158,2,103,182,118,17,145,201,111,28,165,53,161,21,245,142,13,102,48,227,153,145,218,70],
			36 => [120,104,107,109,102,161,76,3,91,191,147,169,182,194,225,120,215,106,155,130,62,127,99,169,124,185,176,78,47,18,151,254,120,77,227,148],
		];

		if (!isset($generators[$ec_count])) return array_fill(0, $ec_count, 0);

		$gen = $generators[$ec_count];
		$result = array_merge($data, array_fill(0, $ec_count, 0));

		for ($i = 0; $i < count($data); $i++) {
			$coef = $result[$i];
			if ($coef === 0) continue;

			$log_coef = qr_gf_log($coef);
			for ($j = 0; $j < $ec_count; $j++) {
				$result[$i + 1 + $j] ^= qr_gf_exp(($log_coef + $gen[$j]) % 255);
			}
		}

		return array_slice($result, count($data));
	}

	function qr_gf_exp(int $x): int {
		static $table = null;
		if ($table === null) {
			$table = [];
			$val = 1;
			for ($i = 0; $i < 256; $i++) {
				$table[$i] = $val;
				$val <<= 1;
				if ($val >= 256) $val ^= 0x11D;
			}
		}
		return $table[$x % 255];
	}

	function qr_gf_log(int $x): int {
		static $table = null;
		if ($table === null) {
			$table = [];
			$val = 1;
			for ($i = 0; $i < 255; $i++) {
				$table[$val] = $i;
				$val <<= 1;
				if ($val >= 256) $val ^= 0x11D;
			}
		}
		return $table[$x];
	}
