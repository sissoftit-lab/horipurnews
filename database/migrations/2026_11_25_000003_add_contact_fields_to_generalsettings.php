<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('generalsettings', function (Blueprint $table) {
            foreach (['site_name', 'contact_address', 'contact_email', 'contact_phone', 'meta_author', 'google_site_verification'] as $col) {
                if (!Schema::hasColumn('generalsettings', $col)) {
                    $table->text($col)->nullable();
                }
            }
        });

        // Seed with the values that used to be hardcoded in the views.
        DB::table('generalsettings')->where('id', 1)->update([
            'site_name'                => 'Live News',
            'contact_address'          => 'Rupayan Trade Center, 114 Kazi Nazrul Islam Avenue, Banglamotor, Dhaka 1000',
            'contact_email'            => 'livenews.asia24@gmail.com',
            'contact_phone'            => '+880 1711 611600',
            'meta_author'              => 'Live News',
            'google_site_verification' => 'RqtCqEkjfirUR2nbWgYx0AhETFslXbrSi_eQhxSsg7w',
        ]);
    }

    public function down()
    {
        Schema::table('generalsettings', function (Blueprint $table) {
            $table->dropColumn(['site_name', 'contact_address', 'contact_email', 'contact_phone', 'meta_author', 'google_site_verification']);
        });
    }
};
