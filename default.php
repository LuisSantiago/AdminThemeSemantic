<?php namespace ProcessWire;


if(!defined("PROCESSWIRE")) die();

if(!isset($content)) $content = '';

$searchForm = $user->hasPermission('page-edit') ? $modules->get('ProcessPageSearch')->renderSearchForm() : '';
$version = $adminTheme->version . 'h';

$config->styles->append($config->urls->root . "wire/templates-admin/styles/font-awesome/css/font-awesome.min.css?v=$version");

$config->styles->prepend($config->urls->adminTemplates . "styles/main.min.css");

$config->styles->append($config->urls->adminTemplates . "semantic/semantic.min.css");

/*$config->styles->append("https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.1.8/semantic.min.css");*/

$ext = $config->debug ? "js" : "min.js";

$config->scripts->append($config->urls->root . "wire/templates-admin/scripts/inputfields.$ext?v=$version");
$config->scripts->append($config->urls->root . "wire/templates-admin/scripts/main.js");

/*$config->scripts->append("https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.1.8/semantic.min.js");
*/

$config->scripts->append($config->urls->adminTemplates . "semantic/semantic.min.js");
$config->scripts->append($config->urls->adminTemplates . "scripts/main.min.js");



require_once(dirname(__FILE__) . "/AdminThemeSemanticHelpers.php");
$helpers = $this->wire(new AdminThemeSemanticHelpers());
$extras = $adminTheme->getExtraMarkup();

?><!DOCTYPE html>
<html lang="<?php echo $helpers->_('en'); ?>">
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<meta name="robots" content="noindex, nofollow" />
	<meta name="google" content="notranslate" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<title><?php echo $helpers->renderBrowserTitle(); ?></title>
	<script type="text/javascript"><?php echo $helpers->renderJSConfig(); ?></script>

	<?php

	foreach($config->styles as $file) echo "\n\t<link type='text/css' href='$file' rel='stylesheet' />";
	foreach($config->scripts as $file) echo "\n\t<script type='text/javascript' src='$file'></script>";

	?>

	<?php echo $extras['head']; ?>





</head>
<body class='<?php echo $helpers->renderBodyClass();?>'>


	<?php if($user->isLoggedin()): ?>


		<div id="sidebar" class="ui inverted vertical sidebar menu">


		</div>





		<div id='topnav' class="ui top inverted fixed small menu">
			<div class="ui container">
				<div id='logo' class='header item'><a href='<?php echo $config->urls->admin?>'><img width='130' src="<?php echo $config->urls->adminTemplates?>styles/images/logo.png" alt="ProcessWire" /></a></div>
				<?php echo $helpers->renderSideNavItems();
				echo $helpers->renderAdminShortcuts();
				?>

				<?php if($user->isLoggedin()): ?>

					<div class="ui dropdown link item">
						<i class="fa fa-user" aria-hidden="true"></i>
						<div class="menu">

							<?php
							if($user->hasPermission('profile-edit')): ?>
							<a class='item' href='<?php echo $config->urls->admin; ?>profile/'><?php echo $user->name; ?>&nbsp;&nbsp;<i class="fa fa-user" aria-hidden="true"></i></a>
						<?php endif; ?>
						<a class='item' href='<?php echo $config->urls->admin; ?>login/logout/'><?php echo $helpers->_('Logout'); ?>&nbsp;&nbsp;<i class="fa fa-power-off" aria-hidden="true"></i></a>
					</div>
				</div>
			<?php endif; ?>









			<div class="right menu">

				<div id="search_icon" class="ui item">
					<i class="search icon fa fa-search"></i>
				</div>



				<div id="search_item" class="ui category search link item">
					<div class="ui icon input">
						<input id="search_input" class="prompt" type="text" placeholder="Search...">
						<i class="search icon fa fa-search"></i>
					</div>
					<div class="results"></div>
				</div>









			</div>





			<div id="mobile_icon" class="ui link item">
				<i class="fa fa-bars" aria-hidden="true"></i>
			</div>


		</div>
	</div>











	<div class="pusher">










		<div id="breadcrumbs2" class="ui segment grid row container">
			<div class="column">

				<div class="ui large breadcrumb">
					<?php echo $helpers->renderBreadcrumbs(); ?>
				</div>
			</div>

		</div>


	<?php endif; ?>




	<div id="notices" class="ui container">
		<div class="column">
			<?php echo $helpers->renderAdminNotices($notices);
			echo $extras['notices'];
			?>
		</div>
	</div>




	<?php if(!$input->modal){

		echo '<div id="content" class="ui segment grid row container"><div class="column">';

		if($page->body) echo $page->body;
		echo $content;
		echo $extras["content"];

		echo	'</div></div>';

	}else{
		echo $content;
	};

	?>


	<div id="versionName">
		<span class="ui inverted header">Processwire <?php echo $config->versionName;?></span>
	</div>


	<img id="logo2" width='240' src="<?php echo $config->urls->adminTemplates?>styles/images/logo.png" alt="ProcessWire" />










</div>






</body>
</html>
