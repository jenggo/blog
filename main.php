<?php
	class main {

		protected $f3, $db, $title_blog;

		public function __construct($title_blog = 'jenggo[dot]net', $nama_db = 'blog.db', $root = '/srv/www/blog/') {
			$this->f3 = Base::instance();
			$this->db = new DB\SQL('sqlite:' . $root . $nama_db);
			$this->title_blog = $title_blog;
		}

		// Buat hashing
		protected function hashing($input) {
			$hash = hash('ripemd128', $input);
			return $hash;
		}

		// Buat slug
		protected function slugging($input) {
			$slug = Web::instance()->slug($input);
			return $slug;
		}

		// Buat mapper database
		protected function tabelDB($tabel) {
			$database = new DB\SQL\Mapper($this->db, $tabel);
			return $database;
		}

		protected function loadJS($nama_js) {
			$this->f3->set('file_js', 'js_admin/' . $nama_js. '.js');
		}

		// Buat nampilin template
		protected function loadTemplate($nama_template, $ajax = false, $admin = false) {

			if ($admin == false) {

				// Untuk halaman utama kudu ngeload kategori, artikel & setting
				$kategori = $this->tabelDB('kategori');
				$this->f3->set('list_kategori', $kategori->find("aktif = '1'"));

				$artikel = $this->tabelDB('artikel');
				$this->f3->set('list_artikel_terakhir', $artikel->find("aktif = '1' ORDER BY tanggal DESC LIMIT 5"));

				$settings = $this->tabelDB('setting');
				$this->f3->set('setting', $settings->load(array('id=?', '1')));
				$main_template = 'index.html';
			}
			else
				$main_template = 'admin_index.html';

			if ($ajax == false)
				$this->f3->set('template', $nama_template);
			else
				$main_template = $nama_template;

			echo Template::instance()->render($main_template);
		}

		// Buat pagination (pake plugin tambahan - ga ada di f3 versi 3.1.2)
		protected function templatePagination($dashboard = false, $limit, $utama, $tambahan = null, $filter = null, $option_utama = null, $option_tambahan = null, $admin = true, $hal_tambahan = null) {

			$option_utama = (is_null($option_utama)) ? array('order' => 'tanggal DESC') : $option_utama;
			$option_tambahan = (is_null($option_tambahan)) ? '' : $option_tambahan;
			$filter = (is_null($filter)) ? array('id>?', 0) : $filter;

			$tab_utama = $this->tabelDB($utama);
			$page = \Pagination::findCurrentPage();

			$subset = $tab_utama->paginate($page - 1, $limit, $filter, $option_utama);
			$this->f3->set("list_$utama", $subset);

			$pages = new Pagination($subset['total'], $subset['limit']);

			if ($admin != false) {

				$hal_tambahan = (is_null($hal_tambahan)) ? '' : '/' . $hal_tambahan;

				$pages->setTemplate('admin_pagebrowser.html');
				$pages->setLinkPath('/' . $this->f3->get('hal_admin') . $hal_tambahan .'/halaman/');
			}

			$this->f3->set('pagebrowser', $pages->serve());

			if (!is_null($tambahan)) {
				$tab_tambahan = $this->tabelDB($tambahan);
				$this->f3->set("list_$tambahan", $tab_tambahan->find($option_tambahan));
			}

			if ($dashboard != false) {
				$tab_user = $this->tabelDB('user');
				$this->f3->set('list_user', $tab_user->find());
			}
		}
	}
?>