<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->unsignedBigInteger('district_id')->nullable()->after('full_address');
            $table->foreign('district_id')->references('district_id')->on('districts')->nullOnDelete();
        });

        // Migrate existing data
        $districts = \Illuminate\Support\Facades\DB::table('districts')->pluck('district_id', 'district_name');
        
        \Illuminate\Support\Facades\DB::table('addresses')->orderBy('address_id')->chunk(100, function ($addresses) use ($districts) {
            foreach ($addresses as $address) {
                if (isset($address->district) && isset($districts[$address->district])) {
                    \Illuminate\Support\Facades\DB::table('addresses')
                        ->where('address_id', $address->address_id)
                        ->update(['district_id' => $districts[$address->district]]);
                }
            }
        });

        Schema::table('addresses', function (Blueprint $table) {
            $table->dropColumn('district');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->string('district')->nullable()->after('full_address');
        });

        // Migrate data back
        $districts = \Illuminate\Support\Facades\DB::table('districts')->pluck('district_name', 'district_id');
        
        \Illuminate\Support\Facades\DB::table('addresses')->orderBy('address_id')->chunk(100, function ($addresses) use ($districts) {
            foreach ($addresses as $address) {
                if (isset($address->district_id) && isset($districts[$address->district_id])) {
                    \Illuminate\Support\Facades\DB::table('addresses')
                        ->where('address_id', $address->address_id)
                        ->update(['district' => $districts[$address->district_id]]);
                }
            }
        });

        Schema::table('addresses', function (Blueprint $table) {
            $table->dropForeign(['district_id']);
            $table->dropColumn('district_id');
        });
    }
};
