<?php 

function set_mail($select)
{
	$CI =& get_instance();
	$CI->db->select($select);
	$val = $CI->db->get('set_mail')->row_array();
	return $val[$select];
}

function api_xendit()
{
	$CI =& get_instance();
	$val = $CI->db->get('api_xendit')->row()->api_key;
	return $val;
}

function dua_bulan_lalu($tgl1)
{
	$hasil = date('Y-m-d', strtotime('-2 month', strtotime( $tgl1 )));
	return $hasil;
}

function expiry_date($tgl1,$tgl2)
{
	$tanggal = new DateTime($tgl1); 

	$sekarang = new DateTime($tgl2);

	$perbedaan = $tanggal->diff($sekarang);

	//gabungkan
	// echo $perbedaan->y.' selisih tahun.';
	// echo $perbedaan->m.' selisih bulan.';
	// echo $perbedaan->d.' selisih hari.';
	// echo $perbedaan->h.' selisih jam.';
	// echo $perbedaan->i.' selisih menit.';
	$a = ($perbedaan->d * (3600 * 24)) + ($perbedaan->h * 3600) + ($perbedaan->i * 60);
	return $a;
}

function durasi_tgl($tgl1,$tgl2)
{
	$datetime1 = new DateTime($tgl1);
  	$datetime2 = new DateTime($tgl2);
  	$difference = $datetime1->diff($datetime2);
  	return $difference->days;
}

function cek_tarif($tgl,$location,$room)
{
	$CI =& get_instance();

	

	foreach ($CI->db->get_where('smartans_tarif',array('LOCATION_ID'=>$location,'ROOM_NO'=>$room))->result() as $rw) {

		// jk bulan bulan yg di input == bulan berjalan
		// maka ambil start date nya diatas yg di atas tgl buat tagihan

		// jika bulan bulan yg di input == bulan lalu
		// dan jika ada 3 macam maka ambil yg terakhir dari bulan tersebut
		if (strtotime($tgl) == strtotime(date('Y-m'))) {
			if ( ( strtotime($rw->START_DATE) < strtotime(date('Y-m-d')) or strtotime($rw->START_DATE) == strtotime(date('Y-m-d')) ) && strtotime($rw->END_DATE) > strtotime(date('Y-m-d'))  ) {
				return $rw->ID_TARIF;
			} elseif ( ( strtotime($rw->START_DATE) < strtotime(date('Y-m-d')) or strtotime($rw->START_DATE) == strtotime(date('Y-m-d')) ) && strtotime($rw->END_DATE) <= strtotime(date('Y-m-d'))  ) {
				return $rw->ID_TARIF;
			}
		} elseif (strtotime($tgl) < strtotime(date('Y-m'))) {
			$tgl_akhir = akhir_tgl(substr($tgl, 0,4), substr($tgl, 5,7));
			if ( strtotime($rw->START_DATE) < strtotime($tgl_akhir) && strtotime($rw->END_DATE) >= strtotime($tgl_akhir)  ) {
				return $rw->ID_TARIF;
			}
		} else {
			// tgl yg di pilih > dari tanggal berjalan
			return 'gagal';
		}

		
	}
}

function akhir_tgl($year, $month) {
	return date("Y-m-d", strtotime('-1 second', strtotime('+1 month',strtotime($month . '/01/' . $year. ' 00:00:00'))));
}

function bulan_indo($b)
{
	$bulan = array(
        '1' => 'Januari',
        '2' => 'Februari',
        '3' => 'Maret',
        '4' => 'April',
        '5' => 'Mei',
        '6' => 'Juni',
        '7' => 'Juli',
        '8' => 'Agustus',
        '9' => 'September',
        '10' => 'Oktober',
        '11' => 'November',
        '12' => 'Desember',
	);

	return $bulan[$b];

}

function date_indo($tgl)
{
	return date('d F Y', strtotime($tgl));
}

function total_power_usage($location_id,$room_id,$bulan,$tahun)
{
	$CI =& get_instance();
	
	$cek_str = strlen($bulan);
	if ($cek_str == 1) {
		$bulan = '0'.$bulan;
	}
	$total = $CI->db->query("SELECT sum(POWER_USAGE) as total FROM smartans_daily_power_usage where LOCATION_ID='$location_id' AND ROOM_ID='$room_id' AND USAGE_DATE LIKE '$tahun-$bulan%' ")->row()->total;
	return $total;
}

function total_water_usage($location_id,$room_id,$bulan,$tahun)
{
	$CI =& get_instance();
	$cek_str = strlen($bulan);
	if ($cek_str == 1) {
		$bulan = '0'.$bulan;
	}
	$total = $CI->db->query("SELECT sum(WATER_USAGE) AS total FROM SMARTANS_WATER_METER_V where location_id='$location_id' AND room_id='$room_id' AND MDATE LIKE '$tahun-$bulan%' ")->row()->total;
	return $total;
}

function stok_display($id_subkategori)
{
	$CI =& get_instance();
	$sql = "
	SELECT
		((COALESCE(SUM(in_qty),0) - COALESCE(SUM(out_qty),0)) ) AS stok_akhir 
	FROM
		stok_transfer
	WHERE
		id_subkategori='$id_subkategori'
		and milik='display';
	";
	$stok = $CI->db->query($sql)->row()->stok_akhir;
	return $stok;
}

function stok_gudang($id_subkategori)
{
	$CI =& get_instance();
	$sql = "
	SELECT
		((COALESCE(SUM(in_qty),0) - COALESCE(SUM(out_qty),0)) ) AS stok_akhir 
	FROM
		stok_transfer
	WHERE
		id_subkategori='$id_subkategori'
		and milik='gudang';
	";
	$stok = $CI->db->query($sql)->row()->stok_akhir;
	return $stok;
}

function cek_ppn($no_po)
{
	$cek = get_data('po_master','no_po',$no_po,'ppn');
	if ($cek == NULL) {
		$cek = 0;
	}
	return $cek;
}

function cek_return($n,$no)
{
	if ($n == '0') {
		return '<a href="app/ubah_return/'.$no.'" onclick="javasciprt: return confirm(\'Are You Sure ?\')"><label class="label label-info"><i class="fa fa-close"></i></label></a>';
	} else {
		return '<label class="label label-success"><i class="fa fa-check"></i></label>';
	}
}

function create_random($length)
{
    $data = 'ABCDEFGHIJKLMNOPQRSTU1234567890';
    $string = '';
    for($i = 0; $i < $length; $i++) {
        $pos = rand(0, strlen($data)-1);
        $string .= $data{$pos};
    }
    return $string;
}

function upload_gambar_biasa($nama_gambar, $lokasi_gambar, $tipe_gambar, $ukuran_gambar, $name_file_form)
{
    $CI =& get_instance();
    $nmfile = $nama_gambar."_".time();
    $config['upload_path'] = './'.$lokasi_gambar;
    $config['allowed_types'] = $tipe_gambar;
    $config['max_size'] = $ukuran_gambar;
    $config['file_name'] = $nmfile;
    // load library upload
    $CI->load->library('upload', $config);
    // upload gambar 1
    if ( ! $CI->upload->do_upload($name_file_form)) {
    	return $CI->upload->display_errors();
    } else {
	    $result1 = $CI->upload->data();
	    $result = array('gambar'=>$result1);
	    $dfile = $result['gambar']['file_name'];
	    
	    return $dfile;
	}	
}

function get_ph($no_po,$total_h)
{
	$CI =& get_instance();
	// log_r($total_h);
	// if ($total_h = '') {
	// 	$total_h = 0;
	// }
	$ph = $CI->db->get_where('po_master', array('no_po'=>$no_po))->row()->potongan_harga;
	$d_ph = explode(';', $ph);
	$t_h_now = $total_h;
	foreach ($d_ph as $key => $value) {
		if (strstr($value, '%')) {
			$t_persen = str_replace('%', '', $value) /100;
			$n_persen = $t_persen * $t_h_now;
			$t_h_now = $t_h_now - $n_persen;
		} else {
			$t_h_now = $t_h_now - floatval($value);
			// log_r($t_h_now);
		}
	}
	return $t_h_now;

}

function get_diskon_beli($diskon,$total_h)
{
	$CI =& get_instance();
	// log_r($total_h);
	// if ($total_h = '') {
	// 	$total_h = 0;
	// }
	$ph = $diskon;
	$d_ph = explode(';', $ph);
	$t_h_now = $total_h;
	foreach ($d_ph as $key => $value) {
		if (strstr($value, '%')) {
			$t_persen = str_replace('%', '', $value) /100;
			$n_persen = $t_persen * $t_h_now;
			$t_h_now = $t_h_now - $n_persen;
		} else {
			$t_h_now = $t_h_now - floatval($value);
			// log_r($t_h_now);
		}
	}
	return $t_h_now;

}


function get_waktu()
{
	date_default_timezone_set('Asia/Jakarta');
	return date('Y-m-d H:i:s');
}
function select_option($name, $table, $field, $pk, $selected = null,$class = null, $extra = null, $option_tamabahan = null) {
    $ci = & get_instance();
    $cmb = "<select name='$name' class='form-control $class  ' $extra>";
    $cmb .= $option_tamabahan;
    $data = $ci->db->get($table)->result();
    foreach ($data as $row) {
        $cmb .="<option value='" . $row->$pk . "'";
        $cmb .= $selected == $row->$pk ? 'selected' : '';
        $cmb .=">" . strtoupper($row->$field ). "</option>";
    }
    $cmb .= "</select>";
    return $cmb;
}

function get_setting($select)
{
	$CI =& get_instance();
	$data = $CI->db->query("SELECT $select FROM pengaturan_aplikasi where id_pengaturan='1' ")->row_array();
	return $data[$select];
}

function get_data($tabel,$primary_key,$id,$select)
{
	$CI =& get_instance();
	$data = $CI->db->query("SELECT $select FROM $tabel where $primary_key='$id' ")->row_array();
	return $data[$select];
}

function get_produk($barcode,$select)
{
	$CI =& get_instance();
	$data = $CI->db->query("SELECT $select FROM produk where barcode1='$barcode' or barcode2='$barcode' ")->row_array();
	return $data[$select];
}



function alert_biasa($pesan,$type)
{
	return 'swal("'.$pesan.'", "You clicked the button!", "'.$type.'");';
}

function alert_tunggu($pesan,$type)
{
	return '
	swal("Silahkan Tunggu!", {
	  button: false,
	  icon: "info",
	});
	';
}

function selisih_waktu($start_date)
{
	date_default_timezone_set('Asia/Jakarta');
	$waktuawal  = date_create($start_date); //waktu di setting

	$waktuakhir = date_create(date('Y-m-d H:i:s')); //2019-02-21 09:35 waktu sekarang

	//Membandingkan
	$date1 = new DateTime($start_date);
	$date2 = new DateTime(date('Y-m-d H:i:s'));
	if ($date2 < $date1) {
	    $diff  = date_diff($waktuawal, $waktuakhir);
		return $diff->d . ' hari '.$diff->h . ' jam lagi ';
	} else {
		return 'berlangsung';
	}

	

	// echo 'Selisih waktu: ';

	// echo $diff->y . ' tahun, ';

	// echo $diff->m . ' bulan, ';

	// echo $diff->d . ' hari, ';

	// echo $diff->h . ' jam, ';

	// echo $diff->i . ' menit, ';

	// echo $diff->s . ' detik, ';
}



function filter_string($n)
{
	$hasil = str_replace('"', "'", $n);
	return $hasil;
}

function cek_nilai_lulus()
{	
	$CI 	=& get_instance();
	$nilai = $CI->db->query("SELECT sum(nilai_lulus) as lulus FROM mapel ")->row()->lulus;
	return $nilai;
}



function log_r($string = null, $var_dump = false)
    {
        if ($var_dump) {
            var_dump($string);
        } else {
            echo "<pre>";
            print_r($string);
        }
        exit;
    }

    function log_data($string = null, $var_dump = false)
    {
        if ($var_dump) {
            var_dump($string);
        } else {
            echo "<pre>";
            print_r($string);
        }
        // exit;
    }