<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffSarana extends Model
{
    protected $table = 'staff_sarana';

    protected $primaryKey = 'nip';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = ['nip', 'id_akun', 'nama', 'no_hp'];

    public function akun()
    {
        return $this->belongsTo(Akun::class, 'id_akun', 'id_akun');
    }

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'nip', 'nip');
    }
}
