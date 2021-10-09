<?php
require(CONST_PATH_LAYOUT . 'login_dialog.inc.php');
require(CONST_PATH_LAYOUT . 'messages.inc.php');
require(CONST_PATH_LAYOUT . 'scores.inc.php');
require(CONST_PATH_LAYOUT . 'user.inc.php');
require(CONST_PATH_LAYOUT . 'forms.inc.php');
require(CONST_PATH_LAYOUT . 'challenges.inc.php');
require(CONST_PATH_LAYOUT . 'dynamic.inc.php');

use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Extension\CommonMark\Node\Block\IndentedCode;
use League\CommonMark\MarkdownConverter;
use Spatie\CommonMarkHighlighter\FencedCodeRenderer;
use Spatie\CommonMarkHighlighter\IndentedCodeRenderer;


// set global head_sent variable
$head_sent = false;
$converter = null;
$staticVersion = "1.2.4";
$highlightTheme = "base16/atelier-sulphurpool-light";

function head($title = '')
{
	global $head_sent;
	global $staticVersion;
	global $highlightTheme;

	header('Content-Type: text/html; charset=utf-8');
	echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>', ($title ? htmlspecialchars($title) . ' : ' : ''), Config::get('MELLIVORA_CONFIG_SITE_NAME'), ' - ', Config::get('MELLIVORA_CONFIG_SITE_SLOGAN'), '</title>
    <meta name="description" content="', Config::get('MELLIVORA_CONFIG_SITE_DESCRIPTION'), '">
    <meta name="author" content="">
    <link rel="icon" href="/img/favicon.png" type="image/png" />

    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-uWxY/CJNBR+1zjPWmfnSnVxwRheevXITnMqoEIeG1LJrdI0GlVs/9cVSyPYXdcSF" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/highlight.js/11.2.0/styles/' . $highlightTheme . '.min.css">
    <link href="/css/mellivora.css?ver=' . $staticVersion . '" rel="stylesheet">';

	js_global_dict();

	if (Config::get('MELLIVORA_CONFIG_SEGMENT_IO_KEY')) {
		echo '
        <script type="text/javascript">
        window.analytics=window.analytics||[],window.analytics.methods=["identify","group","track","page","pageview","alias","ready","on","once","off","trackLink","trackForm","trackClick","trackSubmit"],window.analytics.factory=function(t){return function(){var a=Array.prototype.slice.call(arguments);return a.unshift(t),window.analytics.push(a),window.analytics}};for(var i=0;i<window.analytics.methods.length;i++){var key=window.analytics.methods[i];window.analytics[key]=window.analytics.factory(key)}window.analytics.load=function(t){if(!document.getElementById("analytics-js")){var a=document.createElement("script");a.type="text/javascript",a.id="analytics-js",a.async=!0,a.src=("https:"===document.location.protocol?"https://":"http://")+"cdn.segment.io/analytics.js/v1/"+t+"/analytics.min.js";var n=document.getElementsByTagName("script")[0];n.parentNode.insertBefore(a,n)}},window.analytics.SNIPPET_VERSION="2.0.9",
        window.analytics.load("', Config::get('MELLIVORA_CONFIG_SEGMENT_IO_KEY'), '");
        window.analytics.page();
        </script>
        ';
	}

	echo '
    </head>
    <body>
    <div class="background"></div>
    <div class="background background-left"></div>
    <div class="background background-right"></div>';

	if (!user_is_logged_in()) {
		login_dialog();
	}

	echo '
    <div class="page">
    <nav class="header" id="header">
        <div id="header-inner">
            <a href="', Config::get('MELLIVORA_CONFIG_SITE_URL'), '">
                <img id="header-logo" src="/img/theme/headerLogo.png">
            </a>
            <div id="header-menu">
                <ul class="nav nav-pills pull-right" id="menu-main">';

	if (user_is_logged_in()) {

		if (user_is_staff()) {
			echo '<li><a href="/admin/">', lang_get('manage'), '</a></li>';
		}

		echo '
                            <li><a href="', Config::get('MELLIVORA_CONFIG_SITE_URL'), 'home">', lang_get('home'), '</a></li>
                            <li><a href="', Config::get('MELLIVORA_CONFIG_SITE_URL'), 'challenges">', lang_get('challenges'), '</a></li>
                            <li><a href="', Config::get('MELLIVORA_CONFIG_SITE_URL'), 'scores">', lang_get('scores'), '</a></li>
                            <li><a href="', Config::get('MELLIVORA_CONFIG_SITE_URL'), 'profile">', lang_get('profile'), '</a></li>
                            ', dynamic_menu_content(), '
                            <li>', form_logout(), '</li>';

	} else {
		echo '
                            <li><a href="', Config::get('MELLIVORA_CONFIG_SITE_URL'), 'home">', lang_get('home'), '</a></li>
                            <li><a href="', Config::get('MELLIVORA_CONFIG_SITE_URL'), 'scores">', lang_get('scoreboard'), '</a></li>
                            ', dynamic_menu_content(), '
                            <li><a href="', Config::get('MELLIVORA_CONFIG_SITE_URL'), 'register">', lang_get('register'), '</a></li>
                            <li><a href="" data-toggle="modal" data-target="#login-dialog">', lang_get('log_in'), '</a></li>';
	}
	echo '
                </ul>
            </div>
        </div>
    </nav><!-- navbar -->

    <div id="background-dots"></div>
    <div class="container" id="body-container">

        <div id="content-container">
        ';

	if (isset($_GET['generic_success'])) {
		message_inline("Action Successful", "green", true, "margin-bottom: 0px");
		spacer();
	} else if (isset($_GET['generic_failure'])) {
		message_inline("Action Failed", "red", true, "margin-bottom: 0px");
		spacer();
	} else if (isset($_GET['generic_warning'])) {
		message_inline("Something Went Wrong", "red", true, "margin-bottom: 0px");
		spacer();
	}

	$head_sent = true;
}

function foot()
{
	global $staticVersion;
	global $highlightTheme;

	echo '</div> <!-- / content container -->
</div> <!-- /container -->

<div id="footer">
    <b>CTFx</b> v1 Beta<br>
	Made with &#x1f499;</a>, Based on <a href="https://github.com/Nakiami/mellivora">mellivora</a>
</div>

</div> <!-- /page -->

<!-- JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-kQtW33rZJAHjgefvhyyzcGF3C5TFyBQBA13V1RKPf4uH+bwyzQxZ6CmMZHmNBEfJ" crossorigin="anonymous"></script>
<script type="text/javascript" src="/js/mellivora.js?ver=' . $staticVersion . '"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/highlight.js/11.2.0/highlight.min.js"></script>
</body>
</html>';
}

function section_title($title, $tagline = '', $decorator_color = "yellow")
{
	echo '
    <div class="row">
        <div class="col-lg-12 page-header">
            <h2 class="typewriter">', title_decorator($decorator_color), htmlspecialchars($title),
		'<small>' . $tagline . '</small>', '
            </h2>
        </div>
    </div>
    ';
}

function section_head($title, $tagline = '', $decorator_color = "yellow", $typewriter = true)
{
	echo '
        <div class="row">
            <div class="col-lg-12" style="margin-bottom: 5px">
                <h2 ', $typewriter ? 'class="typewriter"' : '', '>', title_decorator($decorator_color), htmlspecialchars($title),
		'<small>' . $tagline . '</small>', '
                </h2>
            </div>
        </div>
    ';
}

function section_subhead($title, $tagline = '', $strip_html = true)
{
	echo '
    <div class="row">
        <div class="col-lg-12">
          <h3 class="page-header">', ($strip_html ? htmlspecialchars($title) : $title), ' ', ($tagline ? $strip_html ? '<small>' . htmlspecialchars($tagline) . '</small>' : '<small>' . $tagline . '</small>' : ''), '</h3>
        </div>
    </div>
    ';
}


function title_decorator($color, $rotation = "0deg", $img = "arrow.png")
{
	$colorcode = "#D2D2D9";

	switch ($color) {
		case "blue":
			$colorcode = "#00205c";
			break;
		case "yellow":
			$colorcode = "#ffc629";
			break;
		case "red":
			$colorcode = "#ce0e2d";
			break;
		default:
			break; // default: remains gray
	}

	echo '<div class="title-decorator-container title-decorator-', htmlspecialchars($color), '" style="transform: rotate(', $rotation, ')">
        <div class="title-decorator" style="background-color:', htmlspecialchars($colorcode), '"></div>
        <div class="title-decorator title-decorator-gray"></div>
        <div class="title-decorator title-decorator-icon" style="background-image: url(\'/img/ui/', $img, '\')"></div>
    </div>';
}

function spacer()
{
	echo '<div style="margin-top:5px"></div>';
}

function tag($text)
{
	echo '<div class="inline-tag">', $text, '</div>';
}

function icon($img)
{
	echo '<span class="icon" style="background-image:url(\'/img/ui/', $img, '\')"></span>';
}

function card_simple($title, $content = "", $icon = "")
{
	echo '<div class="ctfx-simple-card">';

	if (!empty ($icon))
		echo '<img class="card-icon" src="', htmlspecialchars($icon), '">';

	echo '<div class="card-content">';
	echo '<div class="card-title">', htmlspecialchars($title), '</div>';

	if (!empty ($content))
		echo '<div class="card-text">', htmlspecialchars($content), '</div>';

	echo '</div></div>';
}

function dropdown($name, $options = null)
{
	if (count($options) <= 1) {
		echo '<div class="btn-group">
            <a href="', $options[0][1], '" class="btn btn-2 btn-xs">', $name, '</a>
        </div>';
	} else if (count($options) > 1) {
		echo '<div class="btn-group">
            <button class="btn btn-2 dropdown-toggle btn-xs" data-toggle="dropdown">', $name, ' <span class="caret"></span></button>
            <ul class="dropdown-menu">';

		foreach ($options as $option) {
			echo '<li><a href="', $option[1], '">', $option[0], '</a></li>';
		}
		echo '</ul>
        </div>';
	}
}

function edit_link($url, $contents, $icon = "", $tooltip = "", $color = "black")
{
	switch ($color) {
		case "white":
			$color = "#F5F5F5";
			break;
		case "gray":
			$color = "#B0B0B0";
			break;
		case "black":
			$color = "#222222";
			break;
		default:
			break;
	}

	echo '<a href="', htmlspecialchars($url), '" style="color: ', htmlspecialchars($color), '">', $contents, '</a>';

	if (!empty ($icon)) {
		echo ' <span class="glyphicon ', htmlspecialchars($icon);

		if (!empty ($tooltip)) {
			echo ' has-tooltip" title="', htmlspecialchars($tooltip), '"
            data-toggle="tooltip" data-placement="top"';
		} else
			echo '"';

		echo "></span>";
	}
}

function button_link($text, $url)
{
	return '<a href="' . htmlspecialchars($url) . '" class="btn btn-xs btn-1">' . htmlspecialchars($text) . '</a>';
}

function menu_management()
{
	echo '<div id="menu-management" class="menu">';
	dropdown("Dashboard", [["Dashboard", "/admin/"]]);
	dropdown("Submissions", [["List submissions", "/admin/submissions"]]);
	dropdown("Users", [["List users", "/admin/users"], ["Bulk Registration", "/admin/bulk_register"]]);
	dropdown("Email", [["Send Email", "/admin/new_email"], ["Send Email to all users", "/admin/new_email?bcc=all"]]);
	dropdown("Exceptions", [["List exceptions", "/admin/exceptions"]]);
	dropdown("Search", [["Search", "/admin/search"]]);
	dropdown("Edit CTF", [["Edit", "/admin/edit_ctf"]]);
	dropdown("Email Rules", [["Regex Rules", "/admin/list_restrict_email"], ["Test Email", "/admin/test_restrict_email"], ["Email Whitelist", "/admin/list_email_whitelist"]]);
	dropdown("Edit Site", [["Dynamic Menu", "/admin/list_dynamic_menu"], ["Dynamic Pages", "/admin/list_dynamic_pages"]]);
	echo '</div>';
}

function js_global_dict()
{

	$dict = array();
	if (user_is_logged_in()) {
		$dict['user_id'] = $_SESSION['id'];
	}

	echo '<script type="text/javascript">
        var global_dict = {};
        ';

	foreach ($dict as $key => $val) {
		echo 'global_dict["', htmlspecialchars($key), '"] = "', htmlspecialchars($val), '"';
	}

	echo '
    </script>';
}

function progress_bar($percent, $type = false, $striped = true)
{

	if (!$type) {
		$type = ($percent >= 100 ? 'success' : 'info');
	}

	echo '
    <div class="progress', $striped ? ' progress-striped' : '', '">
        <div class="progress-bar progress-bar-', $type, '" role="progressbar" aria-valuenow="', $percent, '" aria-valuemin="0" aria-valuemax="100" style="width: ', $percent, '%">
            <span class="sr-only">', $percent, '% complete</span>
        </div>
    </div>
    ';
}

function print_ri($val)
{
	echo '<pre>', print_r($val), '</pre>';
}

function country_flag_link($country_name, $country_code, $return = false)
{
	$country_name = htmlspecialchars($country_name);
	$country_code = htmlspecialchars($country_code);

	$flag_link = '<a class="country-flag" href="country?code=' . htmlspecialchars($country_code) . '">' .
		'<img src="/img/flags/' . $country_code . '.png" class="has-tooltip" data-toggle="tooltip" data-placement="right" alt="' . $country_code . '" title="' . $country_name . '"/>' .
		'</a>';

	if ($return) {
		return $flag_link;
	}

	echo $flag_link;
}

function pager_filter_from_get($get)
{
	if (array_get($get, 'from') != null) {
		unset($get['from']);
	}
	return http_build_query($get);
}

function pager($base_url, $max, $per_page, $current)
{
	if (isset($current)) {
		validate_integer($current);
	}

	// by default, we add on any get parameter to the pager link
	$get_argument_string = pager_filter_from_get($_GET);
	if (!empty($get_argument_string)) {
		$base_url .= pager_url_param_joining_char($base_url) . $get_argument_string;
	}

	$base_url .= pager_url_param_joining_char($base_url);

	$first_start = 0;
	$first_end = $first_start + $per_page * 4;

	if ($current >= $first_end) {
		$first_end -= $per_page;
		$middle_start = $current - $per_page;
		$middle_end = $middle_start + $per_page * 2;
	} else {
		$middle_start = 0;
		$middle_end = 0;
	}

	$last_start = $max - $per_page * 2;
	$last_end = $max;

	echo '<div class="ctfx-pager">',
		'<a class="pager-arrow" style="margin-right:5px" href="' . htmlspecialchars($base_url) . 'from=' . max(0, ($current - $per_page)) . '">◀</a>',
	'<a class="btn btn-xs btn-2 ', (!$current ? 'active' : ''), '" href="', htmlspecialchars($base_url), '">', min(1, $max), '-', min($max, $per_page), '</a>';

	$i = $per_page;
	while ($i < $max) {

		// are we in valid range to display buttons?
		if (
			!($i >= $first_start && $i <= $first_end)
			&&
			!($i >= $middle_start && $i <= $middle_end)
			&&
			!($i >= $last_start && $i <= $last_end)
		) {
			$i += $per_page;
			continue;
		}

		echo '<a class="btn btn-xs btn-2 ', ($current == $i ? 'active' : ''), '" href="', htmlspecialchars($base_url), 'from=', $i, '">', $i + 1, ' - ', min($max, ($i + $per_page)), '</a>';

		$i += $per_page;

		if ((
				(
					($i > $first_end) // if we've passed the first end
					&& // and
					($i - $per_page <= $first_end) // we've just crossed over the line
					&& // and
					($i - $per_page != $middle_start) // we're not adjacent to our middle start
				)
				|| // or
				(
					($i > $middle_end) // if we've passed the current end
					&& // and
					($i - $per_page <= $middle_end) // we've just crossed over the line
				)
			) && ($i + $per_page * 3 < $max) // and we're more than three steps over from the last one
		) {
			echo '<a class="btn btn-xs">...</a>';
		}
	}

	echo '<a class="pager-arrow" href="' . htmlspecialchars($base_url) . 'from=' . min($max - ($max % $per_page), ($current + $per_page)) . '">▶</a>

    </div>';
}

function pager_url_param_joining_char($base_url)
{
	$last_char = substr($base_url, -1);
	if (strpos($base_url, '?') && $last_char != '?' && $last_char != '&') {
		return '&';
	} else {
		return '?';
	}
}

function get_pager_from($val)
{
	if (is_valid_id(array_get($val, 'from'))) {
		return $val['from'];
	}

	return 0;
}

function parse_markdown($text)
{
	global $converter;
	if ($converter === null) {
		$config = [
			'html_input' => 'escape',
			'allow_unsafe_links' => false,
			'max_nesting_level' => 5
		];
		$environment = new Environment($config);
		$environment->addExtension(new CommonMarkCoreExtension());
		$environment->addRenderer(FencedCode::class, new FencedCodeRenderer());
		$environment->addRenderer(IndentedCode::class, new IndentedCodeRenderer());
		$converter = new MarkdownConverter($environment);
	}
	return $converter->convertToHtml($text);
}

