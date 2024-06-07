<?php

/**
 * @param {string} version1
 * @param {string} version2
 * @return {number}
 */
// Compare two version numbers version1 and version2.
// If version1 > version2 return 1, if version1 < version2 return -1, otherwise return 0.
if (!function_exists('compareVersions')) {

	function compareVersions($version1, $version2) {
		$result = 0;

// Split version number parts.
		$parts1 = explode('.', $version1);
		for ($i = 0; $i < count($parts1); $i++) {
			$parts1[$i] = intval($parts1[$i]);
		}

		$parts2 = explode('.', $version2);
		for ($i = 0; $i < count($parts2); $i++) {
			$parts2[$i] = intval($parts2[$i]);
		}

// Match-up version lengths for comparison.
		if (count($parts1) < count($parts2)) {
			for ($i = 0; $i < count($parts2); $i++) {
				$parts1[] = 0;
			}
		}

		if (count($parts2) < count($parts1)) {
			for ($i = 0; $i < count($parts1); $i++) {
				$parts2[] = 0;
			}
		}

// Compare version parts.
		for ($i = 0; $i < count($parts1); $i++) {
			$part1 = $parts1[$i];

			if ($i < count($parts2)) {
				$part2 = $parts2[$i];

				if ($part1 > $part2) {
					$result = 1;
					break;
				}
				else if ($part1 < $part2) {
					$result = -1;
					break;
				}
			}
		}

		return $result;
	}

}