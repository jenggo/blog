<?php
	class blog_admin extends main {

		private function loadUtama() {

			$akses = $this->f3->get('SESSION.akses');
			if ($akses == '1') {
				$id = $this->f3->get('SESSION.id');
				$filter = array('id_user=?', $id);
			}
			else
				$filter = null;

			$this->templatePagination(
				true,
				12,
				'artikel',
				'kategori',
				$filter
			);

			$this->f3->set('tabel_box', 'admin_tabel_normal.html');
			$this->loadJS('dashboard');
		}

		public function utama() {

			$cek = $this->f3->exists('SESSION.login');

			if ($cek) {
				$this->loadUtama();
				$this->loadTemplate('admin_index.html', false, true);
			}
			else {
				$this->loadTemplate('login.html', true); // Kenapa "ajax = true" walaupun bukan request ajax? karena template login berdiri sendiri!
			}
		}

		public function utamaAJAX() {
			$this->loadUtama();
			$this->loadTemplate('admin_dashboard.html', true, true);
		}

		// old-school, ga pake fungsi auth-nya f3
		public function login() {
			$this->f3->clear('SESSION');

			$user = $this->f3->get('POST.username');
			$pass = $this->hashing($this->f3->get('POST.password'));

			$login = $this->tabelDB('user');
			$login->load(array('nama=? AND password=?', $user, $pass));

			if ($login->dry())
				$this->f3->set('SESSION.pesan', 'Username dan atau Password salah!');
			else {
				if ($login->akses > 0) {
					$this->f3->set('SESSION.pesan', 'Berhasil login!');
					$this->f3->set('SESSION.login', $this->hashing(rand()));
					$this->f3->set('SESSION.id', $login->id);
					$this->f3->set('SESSION.nama', $login->nama);
					$this->f3->set('SESSION.akses', $login->akses);
				}
				else
					$this->f3->set('SESSION.pesan', 'Akses ditolak!');
			}

			$this->f3->reroute($this->f3->get('hal_admin'));
		}

		public function logout() {
			$this->f3->clear('SESSION');
			$this->f3->reroute('/');
		}

		public function artikel() {

			$tipe = $this->f3->get('PARAMS.tipe');
			$id = $this->f3->get('PARAMS.id');

			$kategori = $this->tabelDB('kategori');

			switch ($tipe) {
				case 'artikel':
					if (!empty($id)) {
						$artikel = $this->tabelDB('artikel');
						$this->f3->set('artikel', $artikel->load(array('id=?', $id)));
					}
					$this->f3->set('list_kategori', $kategori->find("aktif = '1' ORDER BY nama"));
					$this->loadJS('artikel');
					$this->loadTemplate('admin_artikel.html', true, true);
				break;
				case 'kategori':
					if (!empty($id))
						$this->f3->set('ekategori', $kategori->load(array('id=?', $id)));

					$this->f3->set('list_kategori', $kategori->find());
					$this->loadJS('kategori');
					$this->loadTemplate('admin_kategori.html', true, true);
				break;
			}
		}

		public function komentar() {

			$akses = $this->f3->get('SESSION.akses');

			$option_utama = array('order' => 'id DESC');
			if ($akses == '1') {
				$id = $this->f3->get('SESSION.id');
				$filter = array('kontak=? AND id_user=?', 0, $id);
			}
			else
				$filter = array('kontak=?', 0);

			$this->templatePagination(
				true,
				12,
				'komentar',
				'artikel',
				$filter,
				$option_utama,
				null,
				true,
				'komentar'
			);
			$this->loadTemplate('admin_komentar.html', true, true);
		}

		private function loadUser() {
			$id = $this->f3->get('SESSION.id');
			$filter = array('id!=?', $id);
			$option_utama = array('order' => 'nama');

			$this->templatePagination(
				false,
				12,
				'user',
				null,
				$filter,
				$option_utama,
				null,
				true,
				'users'
			);
		}

		public function user() {
			$this->loadUser();
			$this->f3->set('tabel_box', 'admin_tabel_user.html');
			$this->loadTemplate('admin_user.html', true, true);
		}

		public function userTabel() {
			$this->loadUser();
			$this->loadTemplate('admin_tabel_user.html', true, true);
		}

		public function userEdit() {
			$id = $this->f3->get('PARAMS.id');
			$user = $this->tabelDB('user');
			$this->f3->set('user', $user->load(array('id=?', $id)));

			// if ($this->f3->get('SESSION.akses') == '1')
			// 	$this->loadTemplate('admin_user_edit.html', false, true);
			// else
				$this->loadTemplate('admin_user_edit.html', true, true);
		}

		public function setting() {
			$settings = $this->tabelDB('setting');
			$artikel = $this->tabelDB('artikel');
			$this->f3->set('list_artikel', $artikel->find());
			$this->f3->set('settings', $settings->load(array('id=?', '1')));
			$this->loadTemplate('admin_setting.html', true, true);
		}

		// Simpan data yang dibuat / ubah (khusus kategori, termasuk proses penghapusan data)
		public function simpanData() {

			$tipe = $this->f3->get('PARAMS.tipe');
			$id = $this->f3->get('POST.id');

			switch ($tipe) {
				case 'artikel':

					$artikel = $this->tabelDB('artikel');

					if ($id) $artikel->load(array('id=?', $id));

					$artikel->copyFROM('POST');

					if (!$id) {
						$artikel->id_user = $this->f3->get('SESSION.id');
						$artikel->tanggal = date('Y-m-d');
						$artikel->aktif = '1';
						$artikel->komentar = '1';
						$artikel->pages = '0';
					}

					$artikel->slug = $this->slugging($this->f3->get('POST.judul'));
					$artikel->save();
					echo 'OK!';
				break;
				case 'kategori':
					$nama = $this->f3->get('POST.nama');

					$kategori = $this->tabelDB('kategori');

					if ($id) $kategori->load(array('id=?', $id));
					$kategori->copyFROM('POST');

					if ($id AND !$this->f3->get('POST.aktif')) $kategori->aktif = '0';

					$kategori->slug = $this->slugging($nama);
					$kategori->save();
					// echo ($id) ? 'OK!' : '<a href="' . $this->f3->get('hal_admin') . '/edit/kategori/' . $kategori->id . '">' . $nama . '</a>';
					echo ($id) ? 'OK!' : $nama;
				break;
				case 'user':
					$nama = $this->f3->get('POST.nama');
					$user = $this->tabelDB('user');

					// Cek
					if (!$id) {
						$user->load(array('nama=?', $nama));
						if ($user->dry()) {
							$user->password = '0';
							$user->aktif = '0';
						}
					}
					else {
						// Cek apakah dua password sama!
						$pass1 = $this->f3->get('POST.pass_pertama');
						$pass2 = $this->f3->get('POST.pass_kedua');
						if ($pass1 != $pass2) {
							echo 'BEDA!';
							exit;
						}
						else {
							$user->load(array('id=?', $id));
							$user->password = $this->hashing($pass1);
						}
					}
					$user->copyFROM('POST');
					$user->save();
					echo 'OK!';
				break;
				case 'setting':
					$settings = $this->tabelDB('setting');
					$settings->load(array('id=?', '1'));
					$settings->copyFROM('POST');
					$settings->save();
				break;
			}
		}

		public function hapusData() {
			$tipe = $this->f3->get('PARAMS.tipe');
			$id = $this->f3->get('PARAMS.id');
			$gagal = false;

			$data = $this->tabelDB($tipe); // Load tabel berdasarkan 'tipe'
			$data->load(array('id=?', $id));
			// Cek
			switch ($tipe) {
				case 'user':
					// Cek komentar dari user ini
					$komentar = $this->tabelDB('komentar');
					$komentar->load(array('id_user=?', $id));
					if (!$komentar->dry()) {
						echo 'JANGAN!';
						$gagal = true;
					}
					if ($gagal == true) {
						// Komentar kosong? cek artikel kalau begitu
						$artikel = $this->tabelDB('artikel');
						$artikel->load(array('id_user=?', $id));
						if (!$artikel->dry()) {
							echo 'JANGAN!';
						}
						else
							$gagal = false;
					}
				break;
				case 'kategori':
					// Cek artikel dari kategori ini
					$artikel = $this->tabelDB('artikel');
					$artikel->load(array('id_kategori=?', $id));
					if (!$artikel->dry()) {
						echo 'JANGAN!';
						$gagal = true;
					}
				break;
			}

			// No problem, tidak ada yang nyangkut! hapus data!!
			if ($gagal == false) {
				$data->erase();
				echo 'HAPUS!';
			}
		}

		public function pencarian() {
			$cari = $this->f3->get('POST.pencarian');

			$artikel = $this->tabelDB('artikel');
			$list_artikel = $artikel->find('judul LIKE "%'.$cari.'%" OR isi LIKE "%'.$cari.'%"');

			if ($artikel->dry()) {
				$this->f3->set('list_artikel', $list_artikel);

				$kategori = $this->tabelDB('kategori');
				$list_kategori = $kategori->find();

				$this->f3->set('list_kategori', $list_kategori);

				$this->f3->set('tabel_box', 'admin_tabel_cari.html');
				$this->loadTemplate('admin_dashboard.html');
			}
		}

		public function preview() {
			$isi = $this->f3->get('POST.isi');
			$judul = $this->f3->get('POST.judul');
			if ($isi)
				echo '<h2 class="box-head">' . $judul . '</h2><div class="box-content">' . Markdown::instance()->convert($isi) . '</div>';
		}
	}
?>