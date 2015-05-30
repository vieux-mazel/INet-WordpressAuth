<?php namespace VM\WPLogin\Updates;

use Schema;
use Illuminate\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class CreateWpidsTable extends Migration
{

    public function up()
    {
        Schema::create('vm_wplogin_wpids', function(Blueprint $table) {
            $table->integer('user_id')->unsigned();
            $table->string('wp_id');

            $table->primary(['user_id', 'wp_id']);
            $table->index(['user_id', 'wp_id']);
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('vm_wplogin_wpids');
    }

}
