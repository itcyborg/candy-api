<?php

use GetCandy\Api\Core\Products\Models\ProductFamily;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveAttributeDataFromProductFamilyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $channel = DB::table('channels')->whereDefault(true)->first();

        $families = ProductFamily::all();

        if (Schema::hasColumn('product_families', 'name')) {
            Schema::table('product_families', function (Blueprint $table) {
                $table->dropColumn('name');
            });
            Schema::table('product_families', function (Blueprint $table) {
                $table->text('name')->nullable();
            });
        }

        foreach ($families as $family) {
            $data = json_decode($family->attribute_data, true);
            $name = $data['name'][$channel->handle]['en'];
            // Doing it like this to stop any setters/getters...
            DB::table('product_families')->whereId($family->id)->update([
                'name' => $name,
            ]);
        }
        Schema::table('product_families', function (Blueprint $table) {
            $table->dropColumn('attribute_data');
        });
    }

    public function down()
    {
    }
}
