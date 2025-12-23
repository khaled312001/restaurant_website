<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('s_e_o_s', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('language_id')->nullable();
            $table->text('meta_keyword_home')->nullable();
            $table->text('meta_description_home')->nullable();
            $table->text('meta_keyword_menu')->nullable();
            $table->text('meta_description_menu')->nullable();
            $table->text('meta_keyword_item')->nullable();
            $table->text('meta_description_item')->nullable();
            $table->text('meta_keyword_about_us')->nullable();
            $table->text('meta_description_about_us')->nullable();
            $table->text('meta_keyword_career')->nullable();
            $table->text('meta_description_career')->nullable();
            $table->text('meta_keyword_team_member')->nullable();
            $table->text('meta_description_team_member')->nullable();
            $table->text('meta_keyword_gallery')->nullable();
            $table->text('meta_description_gallery')->nullable();
            $table->text('meta_keyword_faq')->nullable();
            $table->text('meta_description_faq')->nullable();
            $table->text('meta_keyword_blog')->nullable();
            $table->text('meta_description_blog')->nullable();
            $table->text('meta_keyword_contact')->nullable();
            $table->text('meta_description_contact')->nullable();
            $table->text('meta_keyword_reservation')->nullable();
            $table->text('meta_description_reservation')->nullable();
            $table->text('meta_keyword_cart')->nullable();
            $table->text('meta_description_cart')->nullable();
            $table->text('meta_keyword_checkout')->nullable();
            $table->text('meta_description_checkout')->nullable();
            $table->text('meta_keyword_login')->nullable();
            $table->text('meta_description_login')->nullable();
            $table->text('meta_keyword_signup')->nullable();
            $table->text('meta_description_signup')->nullable();
            $table->text('meta_keyword_forget_password')->nullable();
            $table->text('meta_description_forget_password')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('s_e_o_s');
    }
};
