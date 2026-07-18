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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('logo')->nullable();
            $table->string('favicon')->nullable();
            $table->text('footer_about')->nullable();
            $table->string('footer_copyright')->nullable();
            $table->string('contact_address')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('contact_fax')->nullable();
            $table->text('contact_map_iframe')->nullable();
            $table->string('receive_email')->nullable();
            $table->string('receive_email_subject')->nullable();
            $table->text('receive_email_thank_you_message')->nullable();
            $table->integer('total_recent_news_footer')->default(5);
            $table->integer('total_popular_news_footer')->default(5);
            $table->integer('total_recent_news_sidebar')->default(5);
            $table->integer('total_popular_news_sidebar')->default(5);
            $table->integer('total_recent_news_home_page')->default(5);
            $table->string('meta_title_home')->nullable();
            $table->text('meta_keyword_home')->nullable();
            $table->text('meta_description_home')->nullable();
            $table->string('color', 10)->default('#2ecc71');
            $table->string('preloader')->default('On');
            
            // Ativação Modular das Seções (solicitado pelo usuário: 'cada sessao do frontend deve ser modular com funcao de ativar')
            $table->boolean('enable_services')->default(true);
            $table->boolean('enable_doctors')->default(true);
            $table->boolean('enable_testimonials')->default(true);
            $table->boolean('enable_news')->default(true);
            $table->boolean('enable_appointments')->default(true);
            
            // Configurações do PWA (solicitado pelo usuário: 'personalização total pelo painel admin')
            $table->string('pwa_name')->default('Pronto Saúde');
            $table->string('pwa_short_name')->default('ProntoSaude');
            $table->string('pwa_theme_color', 7)->default('#007bff');
            $table->string('pwa_background_color', 7)->default('#ffffff');
            $table->string('pwa_icon_512')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
