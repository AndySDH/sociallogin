<?php
/**
 *  @package   AkeebaSocialLogin
 *  @copyright Copyright (c)2016-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 *  @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Plugin\System\SocialLogin\Library\Helper;

// Protect from unauthorized access
defined('_JEXEC') || die();

/**
 * Helpers for SVG images which are output inline in the page's HTML.
 *
 * An inline SVG shares the page's id namespace. A logo which references its own elements by id (e.g. the Google
 * logo's `<clipPath id="a">` used through `clip-path="url(#a)"`) breaks as soon as the same id appears earlier in the
 * page: the browser resolves the reference to the first element with that id. That happens whenever the same logo is
 * rendered twice on one page (for example, a login module next to the login page) and the earlier copy is hidden,
 * or whenever anything else on the page uses the same id.
 */
final class InlineSvg
{
	/**
	 * Gives every id defined inside the SVG markup a unique suffix, and updates the references to those ids
	 * (`url(#id)`, `href="#id"` and `xlink:href="#id"`) to match.
	 *
	 * @param   string  $svg  The SVG markup
	 *
	 * @return  string  The SVG markup with unique ids. Markup without ids is returned unchanged.
	 */
	public static function withUniqueIds(string $svg): string
	{
		$idPattern = '/(?<![\w:-])id\s*=\s*(["\'])([^"\']+)\1/';

		if (!preg_match_all($idPattern, $svg, $matches))
		{
			return $svg;
		}

		$suffix = '-' . bin2hex(random_bytes(4));
		$map    = [];

		foreach (array_unique($matches[2]) as $id)
		{
			$map[$id] = $id . $suffix;
		}

		// The id attributes themselves
		$svg = preg_replace_callback(
			$idPattern,
			fn(array $m): string => 'id=' . $m[1] . $map[$m[2]] . $m[1],
			$svg
		);

		// References in presentation attributes and inline styles, e.g. clip-path="url(#a)" or fill="url('#b')"
		$svg = preg_replace_callback(
			'/url\(\s*(["\']?)#([^"\')\s]+)\1\s*\)/',
			fn(array $m): string => isset($map[$m[2]]) ? 'url(#' . $map[$m[2]] . ')' : $m[0],
			$svg
		);

		// References in href and xlink:href attributes, e.g. <use xlink:href="#c"/>
		return preg_replace_callback(
			'/((?<![\w-])(?:xlink:)?href\s*=\s*)(["\'])#([^"\']+)\2/',
			fn(array $m): string => isset($map[$m[3]]) ? $m[1] . $m[2] . '#' . $map[$m[3]] . $m[2] : $m[0],
			$svg
		);
	}
}
