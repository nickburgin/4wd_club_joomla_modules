<?php
/**
 * @package     Joomla.Site
 * @subpackage  Templates.rkic41site
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;                                                                                    
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

/** @var Joomla\CMS\Document\HtmlDocument $this */

$app = Factory::getApplication();
$wa  = $this->getWebAssetManager();
$date = Factory::getDate();
$thisyear = date_format($date, 'Y');

/* ------------------------------------------------  removed the svg  -------------------------------------- */
// Browsers support SVG favicons
//$this->addHeadLink(HTMLHelper::_('image', 'joomla-favicon.svg', '', [], true, 1), 'icon', 'rel', ['type' => 'image/svg+xml']);
$this->addHeadLink(HTMLHelper::_('image', 'favicon.ico', '', [], true, 1), 'alternate icon', 'rel', ['type' => 'image/vnd.microsoft.icon']);
//$this->addHeadLink(HTMLHelper::_('image', 'joomla-favicon-pinned.svg', '', [], true, 1), 'mask-icon', 'rel', ['color' => '#000']);
//$this->addHeadLink(HTMLHelper::_('image', Uri::base().'media/templates/site/'.$this->template.'/images/favicon.ico', '', [], true, 1), 'alternate icon', 'rel', ['type' => 'image/vnd.microsoft.icon']);
/* ------------------------------------------------  End Changes -------------------------------------- */

// Detecting Active Variables
$option   = $app->input->getCmd('option', '');
$view     = $app->input->getCmd('view', '');
$layout   = $app->input->getCmd('layout', '');
$task     = $app->input->getCmd('task', '');
$itemid   = $app->input->getCmd('Itemid', '');
$sitename = htmlspecialchars($app->get('sitename'), ENT_QUOTES, 'UTF-8');
$menu     = $app->getMenu()->getActive();
$pageclass = $menu !== null ? $menu->getParams()->get('pageclass_sfx', '') : '';

// Template path
$templatePath = 'templates/' . $this->template;

// Color Theme
$paramsColorName = $this->params->get('colorName', 'colors_standard');
$assetColorName  = 'theme.' . $paramsColorName;
$wa->registerAndUseStyle($assetColorName, 'media/templates/site/rkic41site/css/global/' . $paramsColorName . '.css');

// Use a font scheme if set in the template style options
$paramsFontScheme = $this->params->get('useFontScheme', false);

/* ------------------------------------------------  extras  -------------------------------------- */
// extras by Glenn Arkell
$bgColor = $this->params->get('bgColor', 'transparent');
$bgOpac = $this->params->get('bgOpac', '0.5');
// test for transparent
if ($bgColor != 'transparent') {
    $bgColorHex = str_replace('#','',$bgColor);
    $split_hex_color = str_split( $bgColorHex, 2 );
    $r = \hexdec( $split_hex_color[0] );
    $g = \hexdec( $split_hex_color[1] );
    $b = \hexdec( $split_hex_color[2] );
    $pageBGcolor = 'rgba('.$r.','.$g.','.$b.','.$bgOpac.')';
} else {
    //
    if ($this->params->get('bodyBgColor', '')) {
        $pageBGcolor = 'rgba(255,255,255,'.$bgOpac.')';
    }
}



$cardBGColor = $this->params->get('cardBGColor', 'transparent');
$cardBordColor = $this->params->get('cardBordColor', 'transparent');
$btnPbgColor = $this->params->get('btnPbgColor', '#8bafcf;');
$fontStyles       = '';
/* ------------------------------------------------  end extras  -------------------------------------- */

if ($paramsFontScheme)
{
	if (stripos($paramsFontScheme, 'https://') === 0)
	{
		$this->getPreloadManager()->preconnect('https://fonts.googleapis.com/', ['crossorigin' => 'anonymous']);
		$this->getPreloadManager()->preconnect('https://fonts.gstatic.com/', ['crossorigin' => 'anonymous']);
		$this->getPreloadManager()->preload($paramsFontScheme, ['as' => 'style', 'crossorigin' => 'anonymous']);
		$wa->registerAndUseStyle('fontscheme.current', $paramsFontScheme, [], ['media' => 'print', 'rel' => 'lazy-stylesheet', 'onload' => 'this.media=\'all\'', 'crossorigin' => 'anonymous']);

		if (preg_match_all('/family=([^?:]*):/i', $paramsFontScheme, $matches) > 0)
		{
			$fontStyles = '--rkic41site-font-family-body: "' . str_replace('+', ' ', $matches[1][0]) . '", sans-serif;
			--rkic41site-font-family-headings: "' . str_replace('+', ' ', isset($matches[1][1]) ? $matches[1][1] : $matches[1][0]) . '", sans-serif;
			--rkic41site-font-weight-normal: 400;
			--rkic41site-font-weight-headings: 700;';
		}
	}
	else
	{
		$wa->registerAndUseStyle('fontscheme.current', $paramsFontScheme, ['version' => 'auto'], ['media' => 'print', 'rel' => 'lazy-stylesheet', 'onload' => 'this.media=\'all\'']);
		$this->getPreloadManager()->preload($wa->getAsset('style', 'fontscheme.current')->getUri() . '?' . $this->getMediaVersion(), ['as' => 'style']);
	}
}

// Enable assets
$wa->usePreset('template.rkic41site.' . ($this->direction === 'rtl' ? 'rtl' : 'ltr'))
	->useStyle('template.active.language')
	->useStyle('template.user')
	->useScript('template.user')
	->addInlineStyle(":root {
		--hue: 214;
		--template-bg-light: #f0f4fb;
		--template-text-dark: #495057;
		--template-text-light: #ffffff;
		--template-link-color: #2a69b8;
		--template-special-color: #001B4C;
		$fontStyles
	}");

// Override 'template.active' asset to set correct ltr/rtl dependency
$wa->registerStyle('template.active', '', [], [], ['template.rkic41site.' . ($this->direction === 'rtl' ? 'rtl' : 'ltr')]);

// add custom styles
$assetCustomUserName  = 'theme.customuser';
$wa->registerAndUseStyle($assetCustomUserName, 'media/templates/site/rkic41site/css/rkic41site.css');
$wa->addInlineStyle('.card {border-color:'.$cardBordColor.';background-color:'.$cardBGColor.';}', ['position' => 'after'], [], ['template.user']);

/* ------------------------------------------------  start Changes -------------------------------------- */
// Logo file or site title param
if ($this->params->get('brand', 0))
{
    if ($this->params->get('logoSwitch', 0))
    {
    	$logoImage = HTMLHelper::cleanImageURL($this->params->get('logoFile'));
        if ($this->params->get('w100Logo', 0)) {
            $wa->addInlineStyle('.container-header .grid-child {padding:0; max-width:none;}');
            $wa->addInlineStyle('.container-header .navbar-brand (width:100%;} #mainLogo > img.center { margin: 0 auto !important;}');
            //$wa->addInlineStyle('header {background-image:url('.$logoImage->url.') !important; background-size:cover; background-repeat:no-repeat;}');
        }
        $logo = '<img src="' . Uri::root(true) . '/' . $logoImage->url . '" alt="' . $sitename . '">';
    	$logoMob = '<img src="' . Uri::root(true) . '/' . htmlspecialchars($this->params->get('brandMob') ?? '', ENT_QUOTES) . '" alt="' . $sitename . '">';
    }
    elseif ($this->params->get('siteTitle'))
    {
    	$logo = '<span title="' . $sitename . '">' . htmlspecialchars($this->params->get('siteTitle') ?? '', ENT_COMPAT, 'UTF-8') . '</span>';
    }
    else
    {
    	// this is the basic template image which I don't want displayed
        //$logo = HTMLHelper::_('image', 'logo.svg', $sitename, ['class' => 'logo d-inline-block'], true, 0);
        $logo = '';
    }
}
else
{
	// this is the basic template image which I don't want displayed
    $logo = HTMLHelper::_('image', 'logo.svg', $sitename, ['class' => 'logo d-inline-block'], true, 0);
    //$logo = '';
}

if ($this->params->get('brandBG')) {
	$brandBG = HTMLHelper::cleanImageURL($this->params->get('brandBG'));
	//$wa->addInlineStyle('.container-header .grid-child {background:url('.$brandBG->url.') no-repeat center;} .container-header .container-nav {background:none;}');
    if ($this->params->get('w100Brand', 0)) {
        //$wa->addInlineStyle('.container-header .grid-child {padding:0; max-width:none; height: '.$brandBG->attributes['height'].'px;}');
        $brandBG100Style = 'padding:0; max-width:none; height: '.$brandBG->attributes['height'].'px;';
    } else {
        $brandBG100Style = '';
    }
    $brandBGStyle = 'style="background:url('.$brandBG->url.') no-repeat center; '.$brandBG100Style.'"';
} else {
    $brandBGStyle = '';
}


if ($this->params->get('bodyBgColor', '')) {
	$wa->addInlineStyle('body {background-color:'.$this->params->get('bodyBgColor').' !important;}');
	$wa->addInlineStyle('.site-grid .container-component {background-color: '.$pageBGcolor.'; padding:0 10px;}', ['position' => 'after'], [], ['template.user']);
}

if ($this->params->get('pageBG') > '') {
	$pageBG = HTMLHelper::cleanImageURL($this->params->get('pageBG'))->url;
	$wa->addInlineStyle('.site-grid {background-image:url('.$pageBG.'); background-size:cover; background-repeat:no-repeat; background-attachment:fixed;}', ['position' => 'after'], [], ['template.user']);
    if ($this->countModules('footer', true)) {
    	$wa->addInlineStyle('footer {margin-top: 0 !important;}', ['position' => 'after'], [], ['template.user']);
    }
}

if ($this->params->get('brandMob')) {
	$brandMob = HTMLHelper::cleanImageURL($this->params->get('brandMob'))->url;
    $wa->addInlineStyle('@media screen and (max-width: 767px){#mainLogo{display:none;}} @media screen and (min-width: 768px){#mobLogo{display:none;}}');
} else {
    $wa->addInlineStyle('#mobLogo{display:none;}');
}

if ($this->params->get('removeGradient', 0)) {
    $wa->addInlineStyle('.container-header { background-image: none !important; }');
}
if ($this->countModules('toplogin')) {
	$wa->addInlineStyle('@media (max-width: 429px) {.container-header .navbar-brand img {max-width: 85%;min-width:85%;}}');
}
//$wa->addInlineStyle('div.copyright-wrapper {position:absolute; bottom:0; left:0; z-index:1029; margin:15px 0 25px 0; width:100%; padding:0 10px;}');
$wa->addInlineStyle('div.copyright-wrapper {margin:15px 0; width:100%; padding:20px;}');

$wa->addInlineStyle('div.copyright {float:right;}');
$wa->addInlineStyle('div.designer {float:left;} div.designer > a {color:var(--light);}');
$wa->addInlineStyle('ul.mod-login__options > li > a {color:var(--light);}');

if ($this->params->get('colorName') == 'colors_white') {
	$wa->addInlineStyle('.btn-primary {background-color:'.$btnPbgColor.' !important;}');
	$wa->addInlineStyle('.nav-link {color:var(--template-contrast) !important;}');
	$wa->addInlineStyle('.form-check-input:checked, .form-select[multiple] option:checked, [multiple].custom-select option:checked {background-color:'.$btnPbgColor.' !important;}');
	$wa->addInlineStyle('.page-item.active .page-link {color: var(--rkic41site-color-link); background-color: #c1cee1 !important; border-color: #dfe3e7 !important;}');
	$wa->addInlineStyle('.form-select[multiple] option:checked, [multiple].custom-select option:checked {background-color: var(--rkic41site-color-primary-border) !important;}');
}

// tweek the meta data field generator
if ($this->params->get('change_metaGen', 0)) {
    //$this->setMetaData('generator', $this->params->get('new_metaGen'));
}

// tweek the menu colour
if ($this->params->get('changeMenuColor', 0)) {
    $menuColour = $this->params->get('menuColor', 'currentColor');
    $menuBGColour = $this->params->get('menuBGColor', 'transparent');
    $menuHovColour = $this->params->get('menuHovColor', 'var(--primary)');
    $wa->addInlineStyle('.container-header .container-nav { background-color: '.$menuBGColour.' !important; border-radius: 0.2rem;}');
    $wa->addInlineStyle('.container-header .mod-menu, .container-header .mod-menu > li > a, .container-header .mod-menu > li > span { color: '.$menuColour.'; }');
    $wa->addInlineStyle('.container-header .mod-menu > li.active::after, .container-header .mod-menu > li:hover::after { background-color: '.$menuColour.' !important; }');
    $wa->addInlineStyle('.container-header .mod-menu > li.active > a, .container-header .mod-menu > li:hover > a { color: '.$menuHovColour.' !important; }');
}
if ($this->params->get('menuUnderline', 0)) {
    $wa->addInlineStyle('.container-header .mod-menu > li::after { height:0px;}');
}
if ($this->params->get('menuBG100', 0)) {
    $wa->addInlineStyle('.container-header .container-nav100 { background-color: '.$menuBGColour.' !important; padding: 0rem;}');
    $wa->addInlineStyle('.container-header .container-nav100 { max-width:100%;}');
}
if ($this->params->get('setShadowFrame', 0)) {
    $shadowColour = $this->params->get('shadowColor', 'rgba(0,0,0,0.15)');
    $wa->addInlineStyle('.blog-items .blog-item{border:1px solid '.$shadowColour.';border-radius:5px;box-shadow:0.15rem 0.3rem 0.7rem '.$shadowColour.';}.blog-item .item-content{padding:5px}');
    $wa->addInlineStyle('.shadow-r {border:1px solid '.$shadowColour.';box-shadow:0.15rem 0.3rem 0.7rem '.$shadowColour.';}');
    $wa->addInlineStyle('.shadow-l {border:1px solid '.$shadowColour.';box-shadow:-0.3rem 0.4rem 0.4rem '.$shadowColour.';}');
}

if ($this->params->get('colorName') == 'colors_dark') {
	$wa->addInlineStyle('.table, .table-striped > tbody > tr:nth-of-type(2n+1) > * {color: var(--body-color) !important;}');
	$wa->addInlineStyle('.js-stools-container-bar .btn-toolbar .js-stools-btn-clear {background-color: var(--rkic41site-color-hover) !important;}');
	$wa->addInlineStyle('.breadcrumb-item.active {color: var(--body-color) !important;}');

}

if ($this->params->get('footerImg') > '') {
    if ($this->countModules('footer', true)) {
    	$footBG = HTMLHelper::cleanImageURL($this->params->get('footerFile'));
    	//$wa->addInlineStyle('footer {background-image:url('.$footBG->url.') !important; background-size:cover; background-repeat:no-repeat;}');
    	$footBGStyle = 'style="background-image:url('.$footBG->url.') !important; background-size:cover; background-repeat:no-repeat; height: '.$footBG->attributes['height'].'px;';
    }
} else {
    $footBGStyle = '';
    if ($this->params->get('removeGradient', 0)) {
        $wa->addInlineStyle('.footer { background-image: none !important; }');
    }
}

/* ------------------------------------------------  End Changes -------------------------------------- */

$hasClass = '';

if ($this->countModules('sidebar-left', true))
{
	$hasClass .= ' has-sidebar-left';
}

if ($this->countModules('sidebar-right', true))
{
	$hasClass .= ' has-sidebar-right';
}

// Container
$wrapper = $this->params->get('fluidContainer') ? 'wrapper-fluid' : 'wrapper-static';

$this->setMetaData('viewport', 'width=device-width, initial-scale=1');

$stickyHeader = $this->params->get('stickyHeader') ? 'position-sticky sticky-top' : '';
$stickyFooter = $this->params->get('stickyFooter') ? 'position-stickyf sticky-bottom' : '';
$fblink = $this->params->get('fblink','') > '' ? $this->params->get('fblink') : false;
$fbimage = HTMLHelper::_('image', Uri::base().'templates/'.$this->template.'/images/facebook.png', 'FB', [], true, 1);

// Defer font awesome
$wa->getAsset('style', 'fontawesome')->setAttribute('rel', 'lazy-stylesheet');

?>
<!DOCTYPE html>
<html lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
	<jdoc:include type="metas" />
	<jdoc:include type="styles" />
	<jdoc:include type="scripts" />

	<?php if ($this->params->get('googleAnalytics')) : ?>
        <script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo $this->params->get('googleAnalytics'); ?>"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', '<?php echo $this->params->get('googleAnalytics'); ?>', {
                'user_id': '<?php echo $user->name; ?>'
            });
        </script>
	<?php endif; ?>

</head>

<body class="site <?php echo $option
	. ' ' . $wrapper
	. ' view-' . $view
	. ($layout ? ' layout-' . $layout : ' no-layout')
	. ($task ? ' task-' . $task : ' no-task')
	. ($itemid ? ' itemid-' . $itemid : '')
	. ($pageclass ? ' ' . $pageclass : '')
	. $hasClass
	. ($this->direction == 'rtl' ? ' rtl' : '');
?>">
	<header class="header container-header full-width<?php echo $stickyHeader ? ' ' . $stickyHeader : ''; ?>">

		<?php if ($this->countModules('topbar')) : ?>
			<div class="container-topbar">
			<jdoc:include type="modules" name="topbar" style="none" />
			</div>
		<?php endif; ?>

		<?php if ($this->countModules('below-top')) : ?>
			<div class="grid-child container-below-top">
				<jdoc:include type="modules" name="below-top" style="none" />
			</div>
		<?php endif; ?>

		<?php if ($this->params->get('brand', 1)) : ?>
            <div class="grid-child" <?php echo $brandBGStyle; ?>>
				<div class="navbar-brand">
					<a class="brand-logo" href="<?php echo $this->baseurl; ?>/">

                        <?php if ($this->params->get('logoSwitch', 1)) : ?>
                            <div id="mainLogo">
                                <?php echo $logo; ?>
                            </div>
                        <?php elseif ($this->params->get('brandBG')) : ?>
                            <div id="mainLogo" style="">
                            </div>
                        <?php else : ?>
                            <div id="mainLogo">
                                <?php echo $logo; ?>
                            </div>
                        <?php endif; ?>

<?php // ------------------------------------------------  Extra --------------------------------------  ?>
                        <div id="mobLogo">
                            <?php echo $logoMob; ?>
                        </div>
<?php // ------------------------------------------------  End --------------------------------------  ?>
					</a>

					<?php if ($this->params->get('siteDescription')) : ?>
						<div class="site-description"><?php echo htmlspecialchars($this->params->get('siteDescription') ?? ''); ?></div>
					<?php endif; ?>

				</div>
<?php // ------------------------------------------------  Extra --------------------------------------  ?>
				<?php if ($this->countModules('toplogin')) : ?>
					<div class="container-toplogin">
					<jdoc:include type="modules" name="toplogin" style="none" />
					</div>
				<?php endif; ?>
<?php // ------------------------------------------------  End --------------------------------------  ?>

			</div>
		<?php else : ?>
			<div class="grid-child">
				<?php if ($this->countModules('toplogin')) : ?>
					<div class="container-toplogin" style=" display:inline;">
					<jdoc:include type="modules" name="toplogin" style="html5" />
					</div>
				<?php else : ?>
                    <div id="mobLogo" style="max-width:25%;background-image:url(<?php echo $logoMob; ?>) no-repeat right;"></div>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<?php if ($this->countModules('menu', true) || $this->countModules('search', true)) : ?>
			
            <?php if ($this->params->get('menuBG100')) : ?>
            <div class="grid-child container-nav100">
            <?php endif; ?>

            <div class="grid-child container-nav">

				<?php if ($this->countModules('menu', true)) : ?>
					<jdoc:include type="modules" name="menu" style="none" />
				<?php endif; ?>

				<?php if ($this->countModules('search', true)) : ?>
					<div class="container-search">
						<jdoc:include type="modules" name="search" style="none" />
					</div>
				<?php endif; ?>

				<?php if ($fblink) : ?>
					<div class="container-social" style="float:right;padding-left:10px;margin-top:8px;">
						<a href="<?php echo htmlspecialchars($fblink ?? ''); ?>" target="_blank" alt="">
						<img src="<?php echo Uri::base().'templates/'.$this->template.'/images/facebook.png'; ?>" name="social" alt="" />
						</a>
					</div>
				<?php endif; ?>

			</div>

            <?php if ($this->params->get('menuBG100')) : ?>
            </div>
            <?php endif; ?>

		<?php endif; ?>
	</header>

	<div class="site-grid">
		<?php if ($this->countModules('banner', true)) : ?>
			<div class="container-banner full-width">
				<jdoc:include type="modules" name="banner" style="none" />
			</div>
		<?php endif; ?>

		<?php if ($this->countModules('top-a', true)) : ?>
    		<div class="grid-child container-top-a">
    			<jdoc:include type="modules" name="top-a" style="card" />
    		</div>
		<?php endif; ?>

		<?php if ($this->countModules('top-b', true)) : ?>
    		<div class="grid-child container-top-b">
    			<jdoc:include type="modules" name="top-b" style="card" />
    		</div>
		<?php endif; ?>

		<?php if ($this->countModules('sidebar-left', true)) : ?>
    		<div class="grid-child container-sidebar-left">
    			<jdoc:include type="modules" name="sidebar-left" style="card" />
    		</div>
		<?php endif; ?>

		<div class="grid-child container-component">
			<jdoc:include type="modules" name="breadcrumbs" style="none" />
			<jdoc:include type="modules" name="main-top" style="card" />
			<jdoc:include type="message" />
			<main>
    			<jdoc:include type="component" />
			</main>
			<jdoc:include type="modules" name="main-belowcomp" style="none" />
			<jdoc:include type="modules" name="main-bottom" style="card" />
		</div>

		<?php if ($this->countModules('sidebar-right', true)) : ?>
    		<div class="grid-child container-sidebar-right">
    			<jdoc:include type="modules" name="sidebar-right" style="card" />
    		</div>
		<?php endif; ?>

	</div>

<?php /* ------------------------------------------------  Extra -------------------------------------- */ ?>
	<?php if ($this->countModules('main-comp', true)) : ?>
    	<div class="gasite-grid">
    		<div class="gacontainer-main-comp gafull-width">
    			<jdoc:include type="modules" name="main-comp" style="none" />
    		</div>
    	</div>
	<?php endif; ?>
<?php /* ------------------------------------------------  End -------------------------------------- */ ?>

	<?php if ($this->countModules('main-fullwidth', true)) : ?>
        <div class="site">
    		<jdoc:include type="modules" name="main-fullwidth" style="none" />
    	</div>
    <?php endif; ?>

</div>

<div class="site-grid">
	<?php if ($this->countModules('bottom-a', true)) : ?>
		<div class="grid-child container-bottom-a">
			<jdoc:include type="modules" name="bottom-a" style="card" />
		</div>
	<?php endif; ?>

	<?php if ($this->countModules('bottom-b', true)) : ?>
		<div class="grid-child container-bottom-b">
			<jdoc:include type="modules" name="bottom-b" style="card" />
		</div>
	<?php endif; ?>
</div>

<?php if ($this->countModules('footer', true)) : ?>
	<footer class="container-footer footer full-width"<?php echo $stickyFooter ? ' ' . $stickyFooter : ''; ?> <?php echo $footBGStyle; ?>>
		<div class="grid-child">
			<jdoc:include type="modules" name="footer" style="none" />
		</div>
<?php /* ------------------------------------------------  Extra -------------------------------------- */ ?>
		<div class="grid-child">
			<jdoc:include type="modules" name="under-footer" style="inherited" />
		</div>
    	<div class="copyright-wrapper">
        	<div class="copyright" style="color: <?php echo $this->params->get('copyrightColor'); ?>">
    			Copyright &copy; 2021-<?php echo $thisyear.' '.$sitename; ?>.  All rights reserved.
    		</div>
            <div class="designer" style="color: <?php echo $this->params->get('designerColor'); ?>">
    			created by
    			<a href="https://www.addvaluewebsites.com.au" target="_blank" alt="Add Value Websites"
                    style="color: <?php echo $this->params->get('designerColor'); ?>">Add Value Websites
    			</a>
    		</div>
    	</div>
<?php /* ------------------------------------------------  End -------------------------------------- */ ?>
	</footer>
<?php endif; ?>

	<?php if ($this->params->get('backTop') == 1) : ?>
		<a href="#top" id="back-top" class="back-to-top-link" aria-label="<?php echo Text::_('TPL_RKIC41SITE_BACKTOTOP'); ?>">
			<span class="icon-arrow-up icon-fw" aria-hidden="true"></span>
		</a>
	<?php endif; ?>

	<jdoc:include type="modules" name="debug" style="none" />
</body>
</html>
