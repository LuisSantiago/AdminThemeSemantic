<?php namespace ProcessWire;

/**
 * AdminThemeSemanticHelpers.php
 *
 * Rendering helper functions for use with ProcessWire admin theme.
 *
 * __('FOR TRANSLATORS: please translate the file /wire/templates-admin/default.php rather than this one.');
 *
 */

class AdminThemeSemanticHelpers extends WireData {

	public function __construct() {
		if($this->wire('input')->get('test_notices')) {
			$this->message('Message test');
			$this->message('Message test debug', Notice::debug);
			$this->message('Message test markup <a href="#">example</a>', Notice::allowMarkup);
			$this->warning('Warning test');
			$this->warning('Warning test debug', Notice::debug);
			$this->warning('Warning test markup <a href="#">example</a>', Notice::allowMarkup);
			$this->error('Error test');
			$this->error('Error test debug', Notice::debug);
			$this->error('Error test markup <a href="#">example</a>', Notice::allowMarkup);
		}
		$this->wire('modules')->get('JqueryUI')->use('panel');
	}

	/**
	 * Perform a translation, based on text from shared admin file: /wire/templates-admin/default.php
	 *
	 * @param string $text
	 * @return string
	 *
	 */
	public function _($text) {
		static $translate = null;
		if(is_null($translate)) $translate = $this->wire('languages') !== null;
		if($translate === false) return $text;
		$value = __($text, $this->wire('config')->paths->root . 'wire/templates-admin/default.php');
		if($value === $text) $value = parent::_($text);
		return $value;
	}

	/**
	 * Get the headline for the current admin page
	 *
	 * @return string
	 *
	 */
	public function getHeadline() {
		$headline = $this->wire('processHeadline');
		if(!$headline) $headline = $this->wire('page')->get('title|name');
		$headline = $this->wire('sanitizer')->entities1($this->_($headline));
		return $headline;
	}

	/**
	 * Render a list of breadcrumbs (list items), excluding the containing <ul>
	 *
	 * @param bool $appendCurrent Whether to append the current title/headline to the breadcrumb trail (default=true)
	 * @return string
	 *
	 */
	public function renderBreadcrumbs($appendCurrent = true) {

		$out = '';
		$loggedin = $this->wire('user')->isLoggedin();
		$touch = $this->wire('session')->get('touch');
		$separator = "<span class='divider'>/</span>";

		if(!$touch && $loggedin && $this->className() == 'AdminThemeSemanticHelpers') {

			if($this->wire('config')->debug) {
				$label = __('Debug Mode Tools', '/wire/templates-admin/debug.inc');
				$out .=
					"<li><a href='#' title='$label' onclick=\"$('#debug_toggle').click();return false;\">" .
					"<i class='fa fa-cog'></i></a>$separator</li>";
			}

			if($this->wire('process') != 'ProcessPageList') {
				$url = $this->wire('config')->urls->admin . 'page/';
				$tree = $this->_('Tree');
				$out .=
					"<a class='section' href='$url' data-tab-text='$tree' data-tab-icon='sitemap' title='$tree'>" .
					"<i class='fa fa-sitemap'></i></a>$separator";
			}
		}

		foreach($this->wire('breadcrumbs') as $breadcrumb) {
			$title = $breadcrumb->get('titleMarkup');
			if(!$title) $title = $this->wire('sanitizer')->entities1($this->_($breadcrumb->title));
			$out .= "<a class='section' href='{$breadcrumb->url}'>{$title}</a>$separator";
		}

		if($appendCurrent) $out .= "<div class='active section'>" . $this->getHeadline() . "</div>";

		return $out;
	}

	/**
	 * Render the populated shortcuts head button or blank when not applicable
	 *
	 * @return string
	 *
	 */
	public function renderAdminShortcuts() {


		$user = $this->wire('user');
		if($this->wire('user')->isGuest() || !$user->hasPermission('page-edit')) return '';
		$module = $this->wire('modules')->getModule('ProcessPageAdd', array('noInit' => true));
		$data = $module->executeNavJSON(array('getArray' => true));
		$items = array();

		foreach($data['list'] as $item) {
			$items[] = "<a class='item' href='$data[url]$item[url]'><i class='fa fa-fw fa-$item[icon]'></i>&nbsp;$item[label]</a>";
		}

		if(!count($items)) return '';
		$out = implode('', $items);
		$label = $this->getAddNewLabel();

		$out =
			"<div class='ui dropdown link item'>&nbsp;<i class='fa fa-plus-circle'></i>&nbsp;" .
			"<ul class='menu'><div class='header'>$label</div>$out</ul>" .
			"</div>";

		return $out;
	}

	/**
	 * Render runtime notices div#notices
	 *
	 * @param array $options See defaults in method
	 * @param Notices $notices
	 * @return string
	 *
	 */
	public function renderAdminNotices($notices, array $options = array()) {

		if($this->wire('user')->isLoggedin() && $this->wire('modules')->isInstalled('SystemNotifications')) {
			$systemNotifications = $this->wire('modules')->get('SystemNotifications');
			if(!$systemNotifications->placement) return;
		}

		$defaults = array(
			'messageClass' => 'success', // class for messages
			'messageIcon' => 'check-square', // default icon to show with notices

			'warningClass' => 'warning', // class for warnings
			'warningIcon' => 'exclamation-circle', // icon for warnings

			'errorClass' => 'negative', // class for errors
			'errorIcon' => 'exclamation-triangle', // icon for errors

			'debugClass' => 'teal', // class for debug items (appended)
			'debugIcon' => 'bug', // icon for debug notices

			'listMarkup' => "\n\t<ul class='msg list'>{out}</ul>",
			'itemMarkup' => "\n\t\t<li class='ui {class} message'><div class='container'><p><i class='fa fa-fw fa-{icon}'></i>{text}</p></div></li>",
			);

		if(!count($notices)) return '';
		$options = array_merge($defaults, $options);
		$config = $this->wire('config');
		$out = '';

		foreach($notices as $n => $notice) {

			$text = $notice->text;
			if($notice->flags & Notice::allowMarkup) {
				// leave $text alone
			} else {
				// unencode entities, just in case module already entity some or all of output
				if(strpos($text, '&') !== false) $text = html_entity_decode($text, ENT_QUOTES, "UTF-8");
				// entity encode it
				$text = $this->wire('sanitizer')->entities($text);
			}

			if($notice instanceof NoticeError) {
				$class = $options['errorClass'];
				$icon = $options['errorIcon'];
			} else if($notice instanceof NoticeWarning) {
				$class = $options['warningClass'];
				$icon = $options['warningIcon'];
			} else {
				$class = $options['messageClass'];
				$icon = $options['messageIcon'];
			}

			if($notice->flags & Notice::debug) {
				$class .= " " . $options['debugClass'];
				$icon = $options['debugIcon'];
			}

			// indicate which class the notice originated from in debug mode
			if($notice->class && $config->debug) $text = "{$notice->class}: $text";

			// show remove link for first item only

			$replacements = array(
				'{class}' => $class,
				'{icon}' => $notice->icon ? $notice->icon : $icon,
				'{text}' => $text,
				);

			$out .= str_replace(array_keys($replacements), array_values($replacements), $options['itemMarkup']);
		}

		$out = str_replace('{out}', $out, $options['listMarkup']);
		return $out;
	}


	/**
	 * Get navigation title for the given page, return blank if page should not be shown
	 *
	 * @param Page $c
	 * @return string
	 *
	 */
	protected function getPageTitle(Page $c) {
		if($c->name == 'add' && $c->parent->name == 'page') {
			// ProcessPageAdd: avoid showing this menu item if there are no predefined family settings to use
			$numAddable = $this->wire('session')->getFor('ProcessPageAdd', 'numAddable');
			if($numAddable === null) {
				$processPageAdd = $this->wire('modules')->getModule('ProcessPageAdd', array('noInit' => true));
				if($processPageAdd) {
					$addData = $processPageAdd->executeNavJSON(array("getArray" => true));
					$numAddable = $addData['list'];
				}
			}
			if(!$numAddable) return '';
			$title = $this->getAddNewLabel();
		} else {
			$title = $this->_($c->title);
		}
		$title = $this->wire('sanitizer')->entities1($title);
		return $title;
	}



	public function renderBrowserTitle() {
		$browserTitle = $this->wire('processBrowserTitle');
		if(!$browserTitle) $browserTitle = $this->_(strip_tags($this->wire('page')->get('title|name'))) . ' &bull; ProcessWire';
		if(strpos($browserTitle, '&') !== false) $browserTitle = html_entity_decode($browserTitle, ENT_QUOTES, 'UTF-8'); // we don't want to make assumptions here
		$browserTitle = $this->wire('sanitizer')->entities($browserTitle, ENT_QUOTES, 'UTF-8');
		if(!$this->wire('input')->get('modal')) {
			$httpHost = $this->wire('config')->httpHost;
			if(strpos($httpHost, 'www.') === 0) $httpHost = substr($httpHost, 4); // remove www
			if(strpos($httpHost, ':')) $httpHost = preg_replace('/:\d+/', '', $httpHost); // remove port
			$browserTitle .= ' &bull; ' . $this->wire('sanitizer')->entities($httpHost);
		}
		return $browserTitle;
	}


	public function renderBodyClass() {
		$page = $this->wire('page');
		$modal = $this->wire('input')->get('modal');
		$bodyClass = '';
		if($modal) $bodyClass .= 'modal ';
		if($modal == 'inline') $bodyClass .= 'modal-inline ';
		$bodyClass .= "id-{$page->id} template-{$page->template->name} pw-init";
		if($this->wire('config')->js('JqueryWireTabs')) $bodyClass .= " hasWireTabs";
		if($this->wire('input')->urlSegment1) $bodyClass .= " hasUrlSegments";
		$bodyClass .= ' ' . $this->wire('adminTheme')->getBodyClass();
		return trim($bodyClass);
	}


	public function renderJSConfig() {

		$config = $this->wire('config');

		$jsConfig = $config->js();
		$jsConfig['debug'] = $config->debug;

		$jsConfig['urls'] = array(
			'root' => $config->urls->root,
			'admin' => $config->urls->admin,
			'modules' => $config->urls->modules,
			'core' => $config->urls->core,
			'files' => $config->urls->files,
			'templates' => $config->urls->templates,
			'adminTemplates' => $config->urls->adminTemplates,
			);

		$out =
			"var ProcessWire = { config: " . wireEncodeJSON($jsConfig, true, $config->debug) . " }; " .
			"var config = ProcessWire.config; "; // legacy support

		return $out;
	}

	public function getAddNewLabel() {
		return $this->_('Add New');
	}


	public function renderSideNavItem(Page $p) {

		$isSuperuser = $this->wire('user')->isSuperuser();
		$sanitizer = $this->wire('sanitizer');
		$modules = $this->wire('modules');
		$showItem = $isSuperuser;
		$children = $p->numChildren() ? $p->children("check_access=0") : array();
		if($p->name == 'page' && !$children->has("name=list")) {
			// +PW27: ensure the "Tree" page is shown if it is hidden
			$children->prepend($p->child("name=list, include=hidden"));
		}
		$out = '';
		$iconName = $p->name;
		$icon = $this->wire('adminTheme')->$iconName;
		if(!$icon) $icon = 'fa-sort-desc';
		$numViewable = 0;

		if(!$showItem) {
			$checkPages = count($children) ? $children : array($p);
			foreach($checkPages as $child) {
				if($child->viewable()) {
					$showItem = true;
					$numViewable++;
					if($numViewable > 1) break; // we don't need to know about any more
				}
			}
		}

		// don't bother with a drop-down here if only 1 child
		if($p->name == 'page' && !$isSuperuser && $numViewable < 2) {
			$children = array();
		}

		if(!$showItem) return '';

		if($p->process) {
			$moduleInfo = $modules->getModuleInfo($p->process);
			$textdomain = str_replace($this->wire('config')->paths->root, '/', $this->wire('modules')->getModuleFile($p->process));
		} else {
			$moduleInfo = array();
			$textdomain = '';
		}

		if(!count($children) && !empty($moduleInfo['nav'])) $children = $moduleInfo['nav'];

		$class = strpos($this->wire('page')->path, $p->path) === 0 ? 'active' : ''; // current class
		$class .= count($children) > 0 ? " parent" : ''; // parent class
		$title = $sanitizer->entities1((string) $this->_($p->get('title|name')));
		$currentPagePath = $this->wire('page')->url; // use URL to support sub directory installs


	$out .= "<div class='ui dropdown link small item' style='padding: 0px;'>";

		if(count($children) && WireArray::iterable($children)) {

			$out .= "<a style='padding: 1em 1.5em;' href='$p->url' class='$class $p->name '> $title&nbsp;&nbsp;<i class='fa {$icon}'></i></a>";
			$out .= "<div class='computer-only menu'>";

			foreach($children as $c) {
				$navJSON = '';

				if(is_array($c)) {
					// $c is moduleInfo nav array
					$moduleInfo = array();
					if(isset($c['permission']) && !$this->wire('user')->hasPermission($c['permission'])) continue;
					$segments = $this->input->urlSegments ? implode("/", $this->input->urlSegments) . '/' : '';
					$class = $currentPagePath . $segments == $p->path . $c['url'] ? 'active' : '';
					$title = $sanitizer->entities1(__($c['label'], $textdomain));
					$url = $p->url . $c['url'];
					if(isset($c['navJSON'])) {
						$navJSON = $c['navJSON']; // url part
						$moduleInfo['useNavJSON'] = true;
					}
					$c = $p; // substitute

				} else {
					// $c is a Page object
					if(!$c->process || !$c->viewable()) continue;

					$list = array(
						$this->wire('config')->urls->admin . "page/",
						$this->wire('config')->urls->admin . "page/edit/"
					);

					in_array($currentPagePath, $list) ? $currentPagePath = $this->wire('config')->urls->admin . "page/list/" : '';
					$class = strlen($currentPagePath) && strpos($currentPagePath, $c->url) === 0 ? 'active' : ''; // child current class
					$name = $c->name;

					$moduleInfo = $c->process ? $modules->getModuleInfo($c->process) : array();
					$title = $this->getPageTitle($c);
					if(!strlen($title)) continue;
					$url = $c->url;
					// The /page/ and /page/list/ are the same process, so just keep them on /page/ instead.
					if(strpos($url, '/page/list/') !== false) $url = str_replace('/page/list/', '/page/', $url);
				}

				$quicklinks = '';


				$icon = isset($moduleInfo['icon']) ? $moduleInfo['icon'] : '';
				if($class == 'active' && $icon){
						$out .= "<a href='$url' class='item active $class' data-icon='$icon'><i class='fa fa-$icon'></i>&nbsp;&nbsp;$title</a>";
				}
				else{
						$out .= "<a href='$url' class='item $class' data-icon='$icon'><i class='fa fa-$icon'></i>&nbsp;&nbsp;$title</a>";
				};

				$out .= "";
			}

			$out .= "</div>";

		} else {

			$class = $class ? " class='$class $p->name'" : "class='$p->name'";
			$out .= "<a style='padding: 1em 1.5em;' href='$p->url' class='$class $p->name '> $title&nbsp;&nbsp;</a>";

		}

		$out .= "</div>";

		return $out;
	}


	public function renderSideNavItems() {
		$out = '';
		$admin = $this->wire('pages')->get($this->wire('config')->adminRootPageID);

		foreach($admin->children("check_access=0") as $p) {
			if(!$p->viewable()) continue;
			$out .= $this->renderSideNavItem($p);
		}

		return $out;
	}

}
