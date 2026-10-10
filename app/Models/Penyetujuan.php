<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Penyetujuan extends Model
{
    protected $table = 'penyetujuan';

    protected $primaryKey = 'id_penyetujuan';

    public $timestamps = false;

    protected $fillable = ['id_peminjaman', 'nip', 'status', 'catatan'];

    public function peminjaman()
    {
        return $this->belongsTo(Peminjaman::class, 'id_peminjaman', 'id_peminjaman');
    }

    public function staffSarana()
    {
        return $this->belongsTo(StaffSarana::class, 'nip', 'nip');
    }
}
