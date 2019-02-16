<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/10
 * Time: 14:32
 */
return array(
    // 错误码
    'error_4001'               => 'Meminta kesalahan metode',
    'error_4002'               => 'Parameter permintaan tidak boleh kosong',
    'error_4003'               => 'Kesalahan kode verifikasi',
    'error_4004'               => 'Silakan isi kata sandi akun Anda',
    'error_4005'               => 'Kata sandi akun salah',
    'error_4006'               => 'Akun dinonaktifkan, silakan hubungi administrator',
    'error_4007'               => 'Pengguna ini sudah ada',
    'error_4008'               => 'Kesalahan jaringan, silakan hubungi administrator',
    'error_4009'               => 'Ada peran di bawah perusahaan, tidak dapat menghapus perusahaan',
    'error_4010'               => 'Kesalahan saat mengunggah gambar',
    'error_4011'               => 'Kesalahan saat mengunggah OSS',
    'error_4012'               => 'Status perusahaan koperasi tidak cocok',
    'error_4013'               => 'Perusahaan akun dinonaktifkan, silakan hubungi administrator',
    'error_4014'               => 'Akun administrator perusahaan sudah ada',
    'error_5001'               => 'Jangan timpa operasi',
    'error_8001'               => 'Login kedaluwarsa',
    'error_4015'               => 'Harap isi alasannya mengapa tidak',
    'error_4016'               => 'Harap isi catatan',

    // 通用部分
    'is_login'                 => 'Sudah masuk',
    'success'                  => 'Selesai',
    'is_status'                => 'Telah berubah keadaan, jangan ulangi',
    'data_empty'               => 'Data kosong',
    'order_status_fail'        => 'Status pesanan tidak cocok',
    'auto_mode_no_order'       => 'Pesanan belum ditemukan untuk mengalokasikan',
    'auto_mode_no_user'        => 'Perusahaan tidak dapat dipercaya',

    // 合同金额语言包
    'data_lf'                  => 'Data terlalu panjang, tidak ada uang sebesar itu, periksa',
    'lyz'                      => 'Zero Whole',
    'ints'                     => 'Seluruh',
    'm_num'                    => '零壹贰叁肆伍陆柒捌玖',
    'm_dw'                     => '分角元拾佰仟万拾佰仟亿',

    //------------------------------------------- 员工系统表头
    // 员工列表
    'personnel_list'           => array(
        'user_name' => 'Nama pemohon',
        'real_name' => 'Nama',
        'email'     => 'Alamat email',
        'role_name' => 'Nama peran',
        'cp_name'   => 'Perusahaan yang dimiliki',
        'add_time'  => 'Buat tanggal',
        'operate'   => 'Operasi',
    ),
    // 员工日志
    'personnel_log'            => array(
        'user_name' => 'Nama pemohon',
        'real_name' => 'Nama',
        'cp_name'   => 'Perusahaan yang dimiliki',
        'log_ip'    => 'IP',
        'log_time'  => 'Waktu operasi',
        'log_info'  => 'Keterangan',
    ),
    // 角色管理
    'role_index'               => array(
        'role_name' => 'Nama peran',
        'role_desc' => 'Deskripsi peran',
        'cp_name'   => 'Perusahaan yang dimiliki',
        'role_info' => 'Keterangan',
        'operate'   => 'Operasi',
    ),

    //------------------------------------------- 业务系统表头
    // 订单列表
    'order_index'              => array(
        'order_no'           => 'SN',
        'name'               => 'Nama pemohon',
        'repay_time'         => 'Harus kembali waktu',
        'phone'              => 'Nomor telepon',
        'source'             => 'Sumber Saluran',
        'add_time'           => 'Buat Tanggal',
        'application_amount' => 'Meminjam jumlah',
        'application_term'   => 'Periode pinjaman',
        'order_status'       => 'Status pesanan',
        'end_time'           => 'Waktu kredit',
        'region_name'        => 'Region',
        'due_day'            => 'Hari yang lewat',
        'handle_state'            => 'Status surat',
        'pay_status'            => 'Status peminjaman',
        'risk_status'            => 'Status kontrol angin',
    ),
    // 客户列表
    'user_list'                => array(
        'name'     => 'Nama pemohon',
        'idcode'   => 'Nomor ID',
        'phone'    => 'Nomor telepon',
        'source'   => 'Sumber Saluran',
        'reg_time' => 'Waktu pendaftaran',
    ),
    // 订单详情
    'order_info'               => array(
        'order_no'           => 'SN',
        'name'               => 'Nama pemohon',
        'phone'              => 'Nomor telepon',
        'application_amount' => 'Meminjam jumlah',
        'paid_amount'        => 'Jumlah pembayaran',
        'bankcard_name'      => 'Akun bank',
        'card_num'           => 'Nomor kartu bank',
        'add_time'           => 'Waktu pinjaman',
        'refuse_time'        => 'Waktu persetujuan',
        'lending_time'       => 'waktu peminjaman',
        'end_time'           => 'Waktu penyelesaian pembayaran',
        'order_status'       => 'Status',
        'not_pass_info'      => 'Not passed reason',
    ),
    // 借款扣款记录信息
    'pay_log'                  => array(
        'cmd'       => 'Jenis pemberitahuan',
        't_id'      => 'Nomor pesanan perdagangan',
        'bt_id'     => 'Rekonsiliasi akun',
        'status'    => 'Status',
        'price'     => 'Jumlah transaksi',
        'currency'  => 'Mata uang transaksi',
        'productid' => 'BluePlay Product ID',
        'add_time'  => 'Waktu pemberitahuan',
    ),
    // 还款扣款记录信息
    'repay_log'                => array(
        'cmd'      => 'Jenis pemberitahuan',
        't_id'     => 'Nomor pesanan perdagangan',
        'status'   => 'Status',
        'price'    => 'Jumlah transaksi',
        'currency' => 'Mata uang transaksi',

        'productid' => 'BluePlay Product ID',
        'add_time'  => 'Waktu pemberitahuan',
    ),
    // 还款扣款记录信息
    'repay_code_log'                => array(
        'code'       => 'Recharge code',
        'add_time'      => 'Waktu generasi',
        'status'    => 'Negara',
    ),
    // 费用配置
    'system_list'              => array(
        'type_id'      => 'Number',
        'company_name' => 'Perusahaan kerjasama',
        'apply_term'   => 'Periode pinjaman',
        'apply_amount' => 'Meminjam jumlah',
        'rate'         => 'Suku bunga',
        'service_fee'  => 'Biaya layanan platform',
        'approval_fee' => 'Tingkat audit informasi',
        'over_fee'     => 'Tarif terlambat',
        'term_fee'     => 'Bunga pembayaran kembali',
        'max_money'    => 'Batas peminjaman harian',
        'operate'      => 'Operasi'
    ),

    //------------------------------------------- 审批系统表头
    // 所有审批
    'order_all'                => array(
        'order_no'           => 'SN',
        'user_name'          => 'Nama pemohon',
        'user_card'          => 'Nomor ID',
        'user_phone'         => 'Nomor telepon',
        'application_amount' => 'Meminjam jumlah',
        'application_term'   => 'Periode pinjaman',
        'create_time'        => 'Buat tanggal',
        'handle_state'       => 'Status Persetujuan',
        'handle_admin'       => 'Approvers',
        'handle_time'        => 'Waktu persetujuan',
        'operate'            => 'Operasi',
    ),
    // 信审订单详细-客户资料
    'order_todo_user'          => array(
        'title'       => 'Informasi pelanggan',
        'phone'       => 'Nomor telepon',
        'name'        => 'Nama',
        'idcode'      => 'Nomor ID',
        'city'        => 'Kota',
        'address'     => 'Alamat',
        'industry'    => 'Industri',
        'profession'  => 'Pekerjaan',
        'gps_address' => 'Alamat GPS',
        'education'   => 'Pendidikan',
        'company'     => 'Nama perusahaan',
        'company_add' => 'Alamat Perusahaan',
        'company_tel' => 'Telepon perusahaan',
        'credit_img'  => 'Gambar tanda tangan',
        'other_img'   => 'Data gambar lainnya'
    ),
    // 信审订单详细-订单
    'order_todo_info'          => array(
        'title'              => 'Detail pesanan',
        'order_no'           => 'SN',
        'name'               => 'Nama pemohon',
        'phone'              => 'Nomor telepon',
        'application_amount' => 'Meminjam jumlah',
        'paid_amount'        => 'Jumlah pembayaran',
        'bankcard_name'      => 'Nama bank',
        'card_num'           => 'Nomor kartu bank',
        'add_time'           => 'Waktu pinjaman',
        'application_term'   => 'Periode pinjaman',
        'refuse_time'        => 'Waktu persetujuan',
        'lending_time'       => 'Waktu peminjaman',
        'end_time'           => 'Waktu penyelesaian pembayaran',
        'order_status'       => 'Status',
        'not_pass_info'      => 'Not passed reason',
        'handle_state'       => 'Pernyataan',
        'success_time'       => 'Waktu untuk Pertanyaan',
        'relaname'           => 'Hubungan',
    ),

    // face++活体记录
    'order_todo_face_log'      => array(
        'title'         => 'Face++live record',
        'add_time'      => 'Waktu permintaan',
        'face_image'    => 'Foto Langsung',
        'image_ref1'    => 'ID foto',
        'image_best'    => 'Gambar terbaik',
        'image_env'     => 'Foto pengenalan wajah palsu',
        'error_message' => 'Prompt kesalahan',
        'match_score'   => 'Skor pertandingan'
    ),

    //------------------------------------------- 合作公司表头
    // 合作公司
    'company_list'             => array(
        'cp_name'           => 'Nama Perusahaan',
        'cp_num'            => 'Akun manajemen perusahaan',
        'cp_code'           => 'Kode perusahaan',
        'cp_leg_person'     => 'Orang hukum perusahaan',
        'cp_contact_person' => 'Kontak perusahaan',
        'cp_mobile'         => 'Telepon perusahaan',
        'cp_address'        => 'Alamat Perusahaan',
        'cp_country'        => 'Negara',
        'status'            => 'Status aplikasi',
        'operator_name'     => 'Auditor',
        'operator_date'     => 'Waktu aplikasi',
        'operate'           => 'Operasi',
    ),

    //------------------------------------------- 统计表头
    // 统计分析 注册量 订单量 逾期订单量 坏账量
    'chart_count_view'         => array(
        'order_count'       => 'Kuantitas pesanan',
        'reg_user_count'    => 'Registrasi',
        'due_order_count'   => 'Pesanan terlambat',
        'death_order_count' => 'Hutang buruk',
    ),
    // 统计分析 放款金额 逾期金额 坏账金额
    'chart_money_count_data'   => array(
        'sum_amount'   => 'Jumlah pinjaman',
        'due_amount'   => 'Jumlah tertunggak',
        'death_amount' => 'Jumlah utang macet',
    ),
    // 统计分析 风控通过率 信审通过率 总通过率
    'chart_pass_count_data'    => array(
        'risk_rate'    => 'Tingkat kontrol risiko',
        'xinshen_rate' => 'Tingkat kelulusan huruf',
        'all_rate'     => 'Tingkat kelulusan total',
    ),
    // 统计分析 当天注册去申请借款的比率
    'chart_one_day_count_data' => array(
        'jiekuan_rate' => 'Nilai permohonan pinjaman pada hari itu'
    ),

    // 统计分析 业务系统 渠道明细
    'get_channel_data_list'    => array(
        "name"                   => "Nama saluran",
        "click"                  => "Klik",
        "download"               => "Unduh",
        "register"               => "Pendaftaran",
        "apply_order"            => "Urutan",
        "download_click_rate"    => "Rasio Unduh / Klik",
        "register_download_rate" => "Registrasi / unduhan bagikan",
        "order_register_rate"    => "Urutan / Pendaftaran",
        "order_click_rate"       => "Rasio Pesanan / Klik",
        "order_download_rate"    => "Order / Unduh Rasio"
    ),

    //新增逾期
    'chart_due_list'           => array(
        'date_str'                   => 'Harus dilunasi',
        'order_pay_sum'              => 'Harus dibayar kembali',
        'order_repay_sum'            => 'Pembayaran aktual',
        'order_today_due_sum'        => 'Jumlah pertama terlambat',
        'order_due_sum'              => 'Nomor terlambat saat ini',
        'first_overdue_rate'         => 'Rasio tertunggak pertama',
        'current_overdue_rate'       => 'Rasio tertunggak saat ini',
        '1_3_days'                   => '1-3 hari',
        '4_8_days'                   => '4-8 hari',
        '9_18_days'                  => '9-18 hari',
        '19_30_days'                 => '19-30 hari',
        '31_60_days'                 => '31-60 hari',
        'over_60_days'               => '61+hari',
        'first_overdue_3_days'       => '3 hari pertama',
        'first_overdue_8_days'       => '8 hari pertama',
        'first_overdue_18_days'      => '18 hari pertama',
        'first_overdue_30_days'      => '30 hari pertama',
        'first_overdue_60_days'      => '60 hari pertama',
        'first_overdue_over_60_days' => 'Pertama di atas 61+ hari',
    ),


    //------------------------------------------- 财务表头


    //------------------------------------------- 财务表头

    // 放款列表
    //'finance_payment'          => array(
    //    'sn'       => '编号',
    //    'order_no' => '订单编号',
    //    'name'     => '用户名',
    //    'price'    => '放款金额',
    //    'add_time' => '放款时间'
    //),
    // 还款列表
    //'finance_repayment'        => array(
    //    'sn'       => '编号',
    //    'order_no' => '订单编号',
    //    'name'     => '用户名',
    //    'bt_no'    => '流水号',
    //    'price'    => '回款金额',
    //    'add_time' => '回款时间'
    //),
    // 财务统计表头
    //'finance_chart'            => array(
    //    'date_str'  => '时间',
    //    'repay_sum' => '回款金额',
    //    'pay_sum'   => '放款金额'
    //),
    // 新财务表头
    'finance_all_list'         => array(
        'yinghuan_order_cnt'    => 'Jumlah pembayaran kembali',
        'yihuan_order_cnt'      => 'Pembayaran kembali',
        'weihuan_order_cnt'     => 'Jumlah yang belum dibayar',
        'yingshou_benjin_sum'   => 'Pokok piutang',
        'yingshou_benxi_sum'    => 'Piutang',
        'yingshou_zongjine_sum' => 'Jumlah total piutang',
        'huankuan_benxi_sum'    => 'Pembayaran kembali',
        'huankuan_zonge_sum'    => 'Pembayaran total',
        'benjin_huishou_rate'   => 'Tingkat pemulihan pokok',
        'yingshou_huishou_rate' => 'Tingkat Pengiriman Uang',
        'zong_huishou_rate'     => 'Total tingkat pembayaran',
    ),
    // 财务放款
    'finance_pay_list'         => array(
        'date_str'          => 'Tanggal pinjaman',
        'order_cnt'         => 'Jumlah pinjaman jatuh tempo',
        'order_success_cnt' => 'Sudah dibayar',
        'order_fail_cnt'    => 'Kegagalan kredit',
        'order_success_sum' => 'Total pinjaman',
        'order_repayment_sum' => 'Pembayaran total',
    ),

    // 财务总计
    'finance_all_sum_name'     => 'Tamu baru / pelanggan lama',
    'finance_all_sum_list'     => array(
        'name'               => 'Tipe pelanggan',
        'order_apply_sum'    => 'Jumlah aplikasi',
        'order_handle_sum'   => 'Masukkan jumlah pena',
        'order_handle_rate'  => 'Over-rate',
        'order_ht_amount'    => 'Jumlah kontrak pinjaman',
        'order_bj_amount'    => 'Jumlah pinjaman aktual',
        'order_repay_sum'    => 'Jumlah total pena yang dikumpulkan',
        'order_repay_amount' => 'jumlah daur ulang total',
        'order_profit'       => 'Laba',
        'order_profit_rate'  => 'Tingkat keuntungan',
    ),

    //------------------------------------------- 2018年7月9日新增表头
    'collection_log'           => array(
        'real_name'        => 'Penerima',
        'order_cnt_sum'    => 'Janji jumlah pembayaran',
        'order_ontime_sum' => 'Nomor terbayar',
        'order_undue_sum'  => 'Jumlah tidak dibayar',
        'order_due_sum'    => 'Periode komitmen terlampaui',
        'rate'             => 'Tingkat pemulihan'
    ),


    //------------------------------------------- 2018年6月8日新增表头

    'chart_click_amount_tag'                  => 'Klik total',
    'chart_channel_ratio_tag'                 => 'Rasio saluran / klik',
    'chart_download_ratio_tag'                => 'Unduh / klik bagikan',
    'chart_download_ratio_tag_success'        => 'Didownload',
    'chart_download_ratio_tag_fail'           => 'Tidak diunduh',
    'chart_channel_download_ratio_tag'        => 'Rasio saluran / unduhan',
    'chart_reg_ratio_tag'                     => 'Registrasi / Rasio Unduhan',
    'chart_reg_ratio_tag_success'             => 'Jumlah yang terdaftar',
    'chart_reg_ratio_tag_fail'                => 'Jumlah tidak terdaftar',
    'chart_channel_reg_ratio_tag'             => 'Rasio saluran / registrasi',
    'chart_apply_ratio_tag'                   => 'Rasio Aplikasi / Registrasi',
    'chart_apply_ratio_tag_success'           => 'Jumlah terapan',
    'chart_apply_ratio_tag_fail'              => 'Jumlah yang belum diterapkan',
    'chart_channel_apply_ratio_tag'           => 'Rasio saluran / aplikasi',
    'chart_new_old_loan_apply_ratio_tag'      => 'Nasabah pinjaman baru dan pinjaman ulang',
    'chart_new_loan_apply_ratio_tag_success'  => 'Jumlah permintaan pinjaman baru',
    'chart_old_loan_apply_ratio_tag_success'  => 'Nomor Aplikasi Kredit Peminjaman',
    'chart_channel_new_loan_apply_ratio_tag'  => 'Aplikasi permintaan pinjaman baru',
    'chart_channel_old_loan_apply_ratio_tag'  => 'Aplikasi pinjaman kredit',
    'chart_risk_pass_ratio_tag'               => 'Proporsi bagian kontrol risiko',
    'chart_risk_pass_ratio_tag_success'       => 'Volume kontrol angin',
    'chart_risk_pass_ratio_tag_fail'          => 'Kontrol angin belum berlalu',
    'chart_channel_risk_pass_ratio_tag'       => 'Saluran / rasio persentase',
    'chart_approval_pass_ratio_tag'           => 'Surat pemeriksaan / rasio kontrol risiko dicatat',
    'chart_approval_pass_ratio_success'       => 'Surat keluaran',
    'chart_approval_pass_ratio_fail'          => 'Surat gagal',
    'chart_channel_approval_pass_ratio_tag'   => 'Rasio saluran / laporan',
    'chart_in_collect_ratio_tag'              => 'Kenaikan',
    'chart_in_collect_ratio_ok'               => 'Pembayaran kembali pada hari jatuh tempo',
    'chart_in_collect_ratio_due'              => 'Jumlah yang belum dibayar pada hari jatuh tempo',
    'chart_channel_in_collect_pass_ratio_tag' => 'Saluran / kelebihan berat pertama',
    'chart_due_order_ratio_tag'               => 'Rasio Keterlambatan / Pesanan',
    'chart_due_day_1'                         => 'Hari terlambat kurang dari 3 pesanan',
    'chart_due_day_2'                         => 'Hari jatuh tempo kurang dari 10 pesanan',
    'chart_due_day_3'                         => 'Hari lewat jatuh tempo kurang dari 15 pesanan',
    'chart_due_day_4'                         => 'Hari-hari yang lewat kurang dari 30 pesanan',
    'chart_due_day_5'                         => 'Hari yang lewat lebih dari 30 pesanan',
    'chart_channel_due_ratio_tag'             => 'Rasio saluran terlewat',
    'chart_due_ratio_tag'                     => 'Rasio terlambat',
    'chart_due_ratio_on_due'                  => 'Terlambat',
    'chart_due_ratio_ok'                      => 'Pembayaran kembali',
    'chart_due_ratio_not_over'                => 'Belum kedaluwarsa',
    'chart_due_three_days_ratio_tag'          => 'PD3Akuntansi',
    'chart_due_ten_days_ratio_tag'            => 'PD10Akuntansi',
    'chart_due_fifteen_days_ratio_tag'        => 'PD15Akuntansi',
    'chart_due_thirty_days_ratio_tag'         => 'PD30Akuntansi',
    'chart_due_over_thirty_days_ratio_tag'    => 'PD30+Akuntansi',
    'chart_finance_yingshou_huikuan_tag'      => 'Daur ulang',
    'chart_finance_yingshou_huikuan_tag_ok'   => 'Telah mengembalikan pokok dan bunga',
    'chart_finance_yingshou_huikuan_tag_fail' => 'Pokok dan bunga yang belum dibayar',
    'chart_finance_sum_huikuan_tag'           => 'Total pembayaran (termasuk penalti)',
    'chart_finance_sum_huikuan_tag_ok'        => 'Pembayaran total',
    'chart_finance_sum_huikuan_tag_fail'      => 'Jumlah total tidak dibayar',
    'chart_finance_loan_repayment_tag'        => 'Laporan daur ulang',
    'chart_finance_principal_tag'             => 'Proses daur ulang utama',

    'chart_loan_repayment_field' => array(
        'title'            => 'Laporan daur ulang',
        'date'             => 'Waktu',
        'loan_amount'      => 'Jumlah total pinjaman',
        'repayment_amount' => 'Pembayaran total',
        'weihuan_amount'   => 'Jumlah hutang',
    ),

    'chart_principal_field' => array(
        'title'              => 'Proses daur ulang utama',
        'benjinhuishou_rate' => 'Tingkat pemulihan pokok',
        'repayment_amount'   => 'Pembayaran total',
        'yinghuan_benjin'    => 'Harus membayar pokoknya',
    ),

    //审批详情-历史纪录
    'history_order_list' => array(
        'order_no'     => 'Nomor pesanan',
        'lending_time' => 'Meminjam waktu',
        'repay_time'   => 'Waktu kedaluwarsa',
        'over_day'    => 'Hari yang lewat',
    ),



    

    // 订单状态
    'order_status'          => array(
        '1'   => 'Kontrol angin Menunggu tinjauan',
        '80'  => 'Untuk ditambahkan',
        '90'  => 'Peninjauan surat Menunggu audit',//Persetujuan
        '100' => 'Persetujuan berlalu',
        '110' => 'Persetujuan gagal',
        '160' => 'Meminjamkan',
        '161' => 'Pembatalan Pinjaman',
        '169' => 'Peminjaman gagal',
        '170' => 'Meminjamkan kesuksesan',
        '180' => 'Jatuh tempo',
        '200' => 'Clearance Pinjaman',
    ),

    // 订单审核状态
    'order_handle'               => array(
        '0' => 'belum selesai',
        '1' => 'Preliminary review',
        '4' => 'Untuk diselesaikan',
        '2' => 'lulus',
        '3' => 'Jangan lewat',
    ),

    // 订单风控状态
    'risk_status'        => array(
        '0' => 'Tidak dilakukan',
        '1' => 'Lewat',
        '2' => 'Tidak lulus',
    ),

    // 订单放款状态
    'pay_status'        => array(
        '0' => 'Tidak dilakukan',
        '1' => 'Sukses',
        '2' => 'Kegagalan',
    ),

    // blue pay 请求回调状态
    'pay_callback_lang'     => array(
        '200' => 'Transaksi sisi BluePay selesai',
        '201' => 'Permintaan kesuksesan BluePay, yang menunjukkan bahwa pesanan berhasil dibuat di sisi BluePay',
        '600' => 'Permintaan bank gagal, umumnya karena informasi rekening kartu bank salah',
        '400' => 'Parameter kesalahan, parameter tidak ada',
        '401' => 'Kesalahan Tanda Tangan / Enkripsi Kesalahan',
        '501' => 'Permintaan bank habis waktunya dan transaksi gagal. Dapat memulai kembali transaksi',
        '506' => 'batas IP',
        '404' => 'Informasi tidak ditemukan, informasi transaksi tidak ditemukan',
        '500' => 'Galat Internal Layanan',
        '646' => 'Pemrosesan bank gagal',
        '601' => 'Saldo pedagang tidak mencukupi dan tidak ada biaya persiapan pinjaman yang cukup. Silakan hubungi BluePay Business Recharge. Uji lingkungan silakan hubungi Layanan Teknis ',
        '649' => 'Informasi bank salah, akun salah'
    ),

    // 行业类型
    'profession_type'            => array(
        '1'  => 'Staf profesional, teknis dan terkait',
        '2'  => 'Administrasi dan Manajemen',
        '3'  => 'Surat dan staf terkait',
        '4'  => 'penjualan',
        '5'  => 'Pekerja Servis',
        '6'  => 'Pertanian, Kehutanan, Perikanan dan Perburuan',
        '7'  => 'produksi dan pekerja terkait, operator peralatan transportasi dan pekerja,',
        '8'  => 'driver',
        '9'  => 'Lainnya',
        '10' => 'Militer',
        '11' => 'Polisi',
        '12' => 'Pengacara',
    ),

    // 职业类型
    'industry_type'         => array(
        '1' => 'Pertanian, kehutanan, berburu dan memancing',
        '2' => 'Menambang / Penggalian',
        '3' => 'Manufaktur',
        '4' => 'Power, gas dan air',
        '5' => 'saluran petelur',
        '6' => 'Perdagangan Grosir / Eceran, Restoran / Hotel',
        '7' => 'Transportasi, penyimpanan dan komunikasi',
        '8' => 'Pembiayaan, Asuransi, Real Estat, Layanan Bisnis',
        '9' => 'Komunitas, Layanan Pribadi Sosial',
    ),

    // 学历
    'education_type'        => array(
        '1' => 'Master dan di atas ',
        '2' => 'Sarjana',
        '3' => 'College',
        '4' => 'Sekolah Tinggi',
        '5' => 'Di bawah sekolah menengah pertama',
    ),

    // 审批跟进记录
    'handle_result_type'           => array(
        '1' => 'Lulus',
        '2' => 'Tolak',
        '3' => 'Tinjauan akhir',
        '4' => 'Penolakan akhir',
    ),

    // 公司审核状态
    'company_status_lang'   => array(
        '0' => 'Melalui',
        '1' => 'Ditolak',
        '2' => 'Menunggu tinjauan',
        '3' => 'Sedang ditinjau',
    ),

    //还款码状态
    'payment_code_status_lang' => array(
        '0' => 'Kadaluarsa',
        '1' => 'Isi ulang',
    ),

    // 通讯录展示时来源
    'phone_from'                 => [
        '1' => 'Aku',
        '2' => 'kontak',
        '3' => 'Komunikasi'
    ],

    // 联系人匹配
    'phone_match'                => [
        'N' => 'Ketidakcocokan',
        'Y' => 'Match',
        'E' => '-',
    ],

    // 催收标记
    'collection_s'               => [
        '0' => 'Semua',
        '1' => 'S1(pd 1~10 Hari)',
        '2' => 'S2(pd 11~30 Hari)',
        '3' => 'S3(pd 31 Hari Di atas)'
    ],

    // 专案查询
    'order_quality'              => [
        '0' => 'Semua',
        '1' => 'Ya',
        '2' => 'Tidak',
    ],

    // 订单信审初审不通过原因
    'order_handle_not_pass_info' => [
        'TL001' => 'Materi tidak sesuai',
        'TL002' => 'Nomor telepon perusahaan tidak aktif',
        'TL003' => 'Informasi perusahaan tidak benar',
        'TL004' => 'Informasi perusahaan tidak dapat diverifikasi',
        'TL005' => 'Pelanggan berkualifikasi rendah',
        'TL006' => 'Info negatif saat verifikasi telepon – lainnya',
        'TL007' => 'Info negatif saat verifikasi via sms',
        'TL008' => 'Yang pinjam bukan dirinya sendiri',
        'TL009' => 'Menolak memberikan informasi wajib',
        'TL010' => 'Nomor HP kontak darurat tidak aktif',
        'TL011' => 'Kontak darurat tidak benar',
        'TL012' => 'Tidak dapat verifikasi data kontak darurat',
        'TL013' => 'Lainnya',
        'TL014' => 'Pengajuan dibatalkan',
        'TL015' => 'Gagal verifikasi data peminjam',
        'TL016' => 'Informasi peminjam tidak dapat diverifikasi',
        'TL017' => 'Tidak dapat menghubungi perusahaan',
        'TL018' => 'Tidak dapat menghubungi kontak darurat',
        'TL019' => 'Tidak dapat menghubungi peminjam',
        'TL020' => 'tidak ada kejanggalan dapat lolos',
        'TL021' => 'Agen yang dicurigai',
        'TL022' => 'Telah pergi dari perusahaan/ pengangguran',
        'TL023' => 'Pekerjaan tidak sesuai',
    ],

    // 订单信审审核记录
    'order_handle_review_log'    => [
        'title'       => 'audit record',
        'add_time'    => 'Waktu persetujuan',
        'admin_name'  => 'Approver',
        'refuse_desc' => 'Alasan penolakan',
        'remark'      => 'Keterangan',
        'review_desc' => 'hasil audit'
    ],

    // 订单电核记录
    'order_handle_flow_log'      => [
        'title'      => 'Rekam listrik',
        'add_time'   => 'follow time',
        'relation'   => 'follow object',
        'status'     => 'Status tindak lanjut',
        'name'       => 'nama',
        'phone'      => 'telepon kontak',
        'remark'     => 'ikuti catatan',
        'admin_name' => 'staf tindak lanjut',
    ],


//测试环境的菜单ID
    'menu_name' => array(
        'approval_name'   => 'Sistem Persetujuan',
        'business_name'   => 'Manajemen Bisnis',
        'company_name'    => 'Perusahaan Kerjasama',
        'collection_name' => 'Sistem Koleksi',
        'personnel_name'  => 'Sistem Karyawan',
        'analysis_name'   => 'Analisis Bisnis',
        'finance_name'    => 'Sistem Keuangan',
        'channel_name'    => 'Saluran Promosi',
        'adv_name'        => 'Saluran Promosi',//渠道备用名称
    ),
    //测试环境的菜单ID
    'menu_child_name'            => array(
        8   => 'Semua Verifikasi', //所有审批
        9   => 'Tinjauan awal',  //未审批（Tidak Disetujui） 修改为待初审
        219   => 'Menunggu tinjauan akhir',  // 待终审
        //业务系统
        10  => 'Daftar Pengguna', //用户列表
        11  => 'Daftar Pesanan',  //订单列表
        12  => 'Saklar Bisnis',   //业务开关
        13  => 'Alokasi Biaya',   //费用配置
//合作公司
        14  => 'Daftar Perusahaan',   //公司列表
        15  => 'Aplikasi Perusahaan', //公司申请
        16  => 'Audit Perusahaan',    //公司审核
//催收系统
        17  => 'Semua Koleksi',       //所有催收
        18  => 'Dalam Koleksi',       //催收中
        19  => 'Pembayaran Kembali',  //已还款
        20  => 'Pengiriman Awal',     //提早派单
        22  => 'Pengurangan Biaya',   //费用减免
//员工系统
        24  => 'Daftar Karyawan',     //员工列表
        25  => 'Manajemen Peran',     //角色管理
        26  => 'Log Karyawan',        //员工日志
        200 => 'Daftar Menu',         //菜单列表
//经营分析
        203 => 'Statistik Bisnis',          //业务统计
        204 => 'Statistik Kontrol Angin',   //风控统计
        209 => 'Statistik Terlambat',       //逾期统计
        215 => 'Laporan Terlambat',         //逾期报表
//推广渠道 Saluran promosi
        202 => 'Daftar Saluran',            //渠道列表 Statistik Channel
//财务系统
        206 => 'Daftar Pembayaran Kembali', //还款统计
        211 => 'Statistik Pinjaman',        //放款统计

    ),

// 线上环境的菜单ID
    'menu_name_online' => array(
        'approval_name'   => 'Sistem Persetujuan',
        'business_name'   => 'Manajemen Bisnis',
        'company_name'    => 'Perusahaan Kerjasama',
        'collection_name' => 'Sistem Koleksi',
        'personnel_name'  => 'Sistem Karyawan',
        'analysis_name'   => 'Analisis Bisnis',
        'finance_name'    => 'Sistem Keuangan',
        'channel_name'    => 'Saluran Promosi',
        'adv_name'        => 'Saluran Promosi',//渠道备用名称
    ),
// 线上环境的菜单ID
    'menu_child_name_online' => array(
        //审批系统
        8   => 'Semua Persetujuan',
        9   => 'Tidak Disetujui',
        220   => 'Menunggu tinjauan akhir',  // 待终审
        //业务管理
        10  => 'Daftar Pengguna',
        11  => 'Daftar Pesanan',
        12  => 'Saklar Bisnis',
        13  => 'Konfigurasi Biaya',
        //合作公司
        14  => 'Daftar Perusahaan',
        15  => 'Aplikasi Perusahaan',
        16  => 'Audit Perusahaan',
        //催收系统
        17  => 'Semua Koleksi',
        18  => 'Dalam Koleksi',
        19  => 'Pembayaran Kembali',
        20  => 'Pengiriman Awal',
        22  => 'Pengurangan Biaya',
        //员工系统
        24  => 'Daftar Karyawan',
        25  => 'Manajemen Peran',
        26  => 'Log Karyawan',
        27  => 'Statistik Ringkasan',
        29  => 'Statistik Pengguna',
        200 => 'Daftar Menu',//菜单列表
        //经营分析

        203 => 'Statistik Bisnis',// 业务统计
        204 => 'Statistik Kontrol Angin', //风控统计
        209 => 'Statistik Terlambat',//逾期统计
        215 => 'Laporan terlambat',//逾期报表
        //财务系统
        213 => 'Statistik Pembayaran Kembali',//还款统计
        211 => 'Statistik Pinjaman',//放款统计
        //推广渠道
        202 => 'Daftar Saluran',//渠道列表
    ),

    // 通话记录状态
    'record_type' => [
        '1' => 'panggilan',
        '2' => 'Keluar',
        '3' => 'tidak terhubung',
    ],


    //jia

    "inser_fail" => "Sisipkan gagal ",

    "cllection_case_details" => "Informasi detail",   //"Kumpulkan Detail", 详细信息
    //催收列表
    'cllection_order_no'     => 'SN',
    'cllection_real_name'    => 'Nama pemohon',
    'cllection_phone'        => 'Nomor telepon',
    'cllection_due_day'      => 'Overdue',
    'cllection_repay_amount' => 'Jumlah hutang',
    'cllection_due_time'     => 'Tanggal jatuh tempo',     //应还日期'Akan mengembalikan tanggal',

    'cllection_collection_status' => 'Status pembayaran',
    'cllection_followup_feed'     => 'Feedback',
    'cllection_case_follow_name'  => 'Kolektor',
    'cllection_success_time'      => "Tanggal Pembayaran Kembali",
    'follow_time'                 => "Waktu Follow Up",      //催收时间 Tanggal koleksi （跟进时间 Waktu follow-up）
    'paid_amount'                 => "Jumlah pembayaran",   //还款金额 Jumlah masuk(入账金额)


    //催收客户用户信息
    "cllection_name"              => "Nama",
    "cllection_sex"               => "Jenis kelamin",    //"Sex",性别
    "cllection_idcode"            => "Nomor ID",        //"ID", 身份证
    "cllection_card_type"         => "Jenis Akun",
    "cllection_bankcard_name"     => "Akun bank",       //"Buka Bank",开户银行
    "cllection_card_num"          => "Nomor Kartu Bank",
    "cllection_is_marrey"         => "Status",          //婚姻状况(已婚：Menikah  未婚 ：Belum  Menikah)  Status
    "cllection_email"             => "E-mail",
    "cllection_education"         => "Tingkat Pendidikan",
    "cllection_relation"          => "Hubungan",

    //催收状态
    'cllection_case_status_0'     => '-',
    'cllection_case_status_170'   => 'Belum melakukan pembayaran', //尚未还款 即催收中
    'cllection_case_status_180'   => 'Belum melakukan pembayaran', //尚未还款 即催收中
    'cllection_case_status_200'   => 'Sudah melakukan pembayaran',  //'Refunded',已还款
    //催收反馈
    'cllection_followup_feed_'    => '-',
    'cllection_followup_feed_0'   => 'Silakan pilih',
    'cllection_followup_feed_181' => 'Komitmen untuk membayar kembali',
    'cllection_followup_feed_182' => 'Negosiasi follow-up',
    'cllection_followup_feed_183' => 'Menolak untuk membayar',
    'cllection_followup_feed_184' => 'User mengaku sudah membayar',
    'cllection_followup_feed_185' => 'Janji untuk mengikuti',
    'cllection_followup_feed_186' => 'Koordinasi',
    'cllection_followup_feed_187' => 'Menolak untuk melaporkan',
    'cllection_followup_feed_188' => 'Yanglainakanmenyampaikan',
    'cllection_followup_feed_189' => 'Tidak ada keterangan',
    'cllection_followup_feed_190' => 'Diverifikasi',
    'cllection_followup_feed_191' => 'Lainnya',
    'cllection_followup_feed_200' => 'Meminta bantuan',
    //催收类型
    'cllection_follow_type_'      => '-',
    'cllection_follow_type_0'     => 'Silakan pilih',
    'cllection_follow_type_81'    => 'Panggilan telepon',
    'cllection_follow_type_82'    => 'Kunjungan eksternal',
    'cllection_follow_type_83'    => 'Pengadilan',
    'cllection_follow_type_84'    => 'Hasil',
    'cllection_follow_type_85'    => 'Ingatkan',
    //手机状态
    'cllection_contact_state_'    => '-',
    'cllection_contact_state_0'   => 'Silakan pilih',
    'cllection_contact_state_1'   => 'Normal',
    'cllection_contact_state_2'   => 'Terputus/diputus',
    'cllection_contact_state_3'   => 'Dialihkan',
    'cllection_contact_state_4'   => 'Mati',
    'cllection_contact_state_5'   => 'Sedang menelepon',
//    'cllection_contact_state_6'   => 'Tidak dapat berkomunikasi',
    'cllection_contact_state_7'   => 'Tidak ada jawaban',

    //关系
    'cllection_target_0'          => 'Silakan pilih',
    'cllection_target_1'          => 'Ayah',
    'cllection_target_2'          => 'lbu',             //母亲
    'cllection_target_3'          => 'Kakak beradik',   //兄弟'Saudara',
    'cllection_target_4'          => 'Suster',          //姐妹
    'cllection_target_5'          => 'Teman',
    'cllection_target_6'          => 'Anak',
    'cllection_target_7'          => 'Rekan kerja',
    'cllection_target_8'          => 'Lainnya',
    'cllection_target_9'          => 'Pasangan',
    'cllection_target_10'         => 'User',

    //性别

    'cllection_sex_0'               => '-',
    'cllection_sex_1'               => 'Pria',
    'cllection_sex_2'               => 'Perempuan',
    //婚姻状态
    'cllection_is_marrey_0'         => 'Belum Menikah',
    'cllection_is_marrey_1'         => 'Menikah',
    //教育程度
    'cllection_education_1'         => 'Master dan di atas',
    'cllection_education_2'         => 'Sarjana',
    'cllection_education_3'         => 'College',
    'cllection_education_4'         => 'Sekolah tinggi',                       //'SMA dan di bawah',高中及以下
    'cllection_education_5'         => 'SMP dan bawah',


    //催收账单详细
    "cllection_principal"           => "Biaya pokok pinjaman",//本金"Kepala Sekolah",
    "cllection_interest"            => "Bunga",
    //"repay_amount"=>"应还金额",
    "cllection_over_interest"       => "Bunga Penalti",
    //"due_day"=>"逾期天数",
    "cllection_repay_data"          => "Informasi Akuntansi",
    //费用流水
    "cllection_price"               => "Jumlah pembayaran",      //入账金额  "Biaya",
    "cllection_add_time"            => "Waktu pembayar",       //"Waktu", 入账时间

    //催收
    "cllection_input"               => "Tolong sampaikan",
    "cllection_personal_id"         => "ID pelanggan",
    "cllection_case_id"             => "ID pesanan",
    "cllection_operator_time"       => "Waktu Follow Up",       //跟进时间"Waktu tindak lanjut",
    "cllection_follow_type"         => "Cara follow Up",        //如何跟进"Mode tindak lanjut",
    "cllection_target"              => "Koleksi objek",
    "cllection_target_name"         => "Nama",
    "cllection_contact_phone"       => "Nomor telepon",         //联系电话"Hubungi Telepon",
    "cllection_contact_state"       => "Keterangan panggilan",  //来电说明  Status Telepon
    "cllection_collection_feedback" => "Feedback",              //催收反馈"Tanggapan Akun", Respon/Jawaban
    "cllection_content"             => "Keterangan",            //跟进记录  Catatan tindak lanjut
    "cllection_operator_name"       => "Kolektor",              //跟进人员"Personil tindak lanjut",


    //费用减免
    "reduction_title"               => "Meminta bantuan",
    "reduction_order_no"            => "SN",
    "reduction_user_name"           => "Nama pemohon",
    "reduction_repay_amount"        => "Jumlah total pengembalian",     //"Pengembalian Total",
    "reduction_over_fee"            => "Penalti Bunga",
    "reduction_fee"                 => "Jumlah pengurangan",
    "reduction_fee_remark"          => "Jumlah pengurangan tidak boleh melebihi jumlah penalti",
    "reduction_remark"              => "Catatan",
    "reduction_apply_has"           => "Sudah ada pengurangan untuk aplikasi yang menunggu persetujuan",
    "reduction_apply_date"          => "Waktu pengajuan", //申请日期（Tanggal Aplikasi）  提交日期（Waktu pengajuan）
    "reduction_apply_name"          => "Pemohon",
    "reduction_application_amount"  => "Biaya pokok pinjaman",  //本金
    "reduction_interest"            => "Bunga", //利息


    "reduction_status"   => "Status Persetujuan",
    "reduction_status_0" => "Menunggu audit",//"Dalam proses",
    "reduction_status_1" => "Melalui",
    "reduction_status_2" => "Ditolak",
    "reduction_record"   => "Terapkan untuk pengurangan biaya",

    //分配催收员

    'cllection_list_no'           => "Nomor",
    //'cllection_real_name' => lang('cllection_real_name'),
    'cllection_role_name'         => "Jenis peran",
    'cllection_has_case'          => "Jumlah kasus yang saat ini ditahan",
    'cllection_can_case'          => "Jumlah Kasus yang Dapat Ditetapkan",

    //视图走势
    "order_view_title"            => "Form orderan",  // Kumpulkan laporan tren pesanan
    "order_view_order_count"      => "Pesanan Total",
    "order_view_collection_count" => "Nomor koleksi sebenarnya",

    "collector_view_title"            => "Checklist Delta",
    "collector_view_order_count"      => "Semua pesanan",
    "collector_view_collection_count" => "Pesanan telah selesai",
    "reduction_view_title"            => "Fee Relief Amount",
    'reduction_view_all_fee'          => 'Jumlah total pesanan',
    'reduction_view_all_num'          => 'Kuantitas pesanan',
    "close_status"                    => "Close_over",
    "has_reduction"                   => "Has reduction",
    "my_relation"                     => "My",
    "no_case"                         => "No Case",
    "no_can"                          => "No Can",
    "cur_yinghuankuan_count"          => "Pembayaran kembali pada hari itu",
    "cur_yihuankuan_count"            => "Pembayaran kembali pada hari yang sama",
    "all_huan_count"                  => "Semua pembayaran kembali",
    "all_yinghuan_count"              => "Semua pembayaran kembali",


);