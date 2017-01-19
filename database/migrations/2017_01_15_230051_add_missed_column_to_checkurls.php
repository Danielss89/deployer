<?php

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use REBELinBLUE\Deployer\CheckUrl;

class AddMissedColumnToCheckurls extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('check_urls', function (Blueprint $table) {
            $table->unsignedInteger('missed')->default(0);
            $table->renameColumn('last_status', 'status');
            $table->renameColumn('title', 'name');
            $table->dateTime('last_seen')->nullable()->default(null);
        });

        CheckUrl::withTrashed()->chunk(100, function (Collection $urls) {
            foreach ($urls as $url) {
                if (is_null($url->status)) {
                    $url->status = CheckUrl::UNTESTED;
                    $url->last_seen = $url->updated_at;
                } elseif ($url->status === CheckUrl::UNTESTED) {
                    $url->status = CheckUrl::OFFLINE;
                }

                $url->save();
            }
        });

        Schema::table('check_urls', function (Blueprint $table) {
            $table->boolean('status')->nullable(false)->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('check_urls', function (Blueprint $table) {
            $table->dropColumn('missed');
            $table->renameColumn('status', 'last_status');
            $table->renameColumn('name', 'title');
            $table->boolean('last_status')->nullable()->default(null)->change();
            $table->dropColumn('last_seen');
        });

        CheckUrl::withTrashed()->chunk(100, function (Collection $urls) {
            foreach ($urls as $url) {
                if ($url->status === CheckUrl::UNTESTED) {
                    $url->status = null;
                } elseif ($url->status === CheckUrl::OFFLINE) {
                    $url->status = 1;
                }

                $url->save();
            }
        });

        Schema::table('check_urls', function (Blueprint $table) {
            $table->boolean('last_status')->nullable()->default(null)->change();
        });
    }
}
