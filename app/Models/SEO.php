<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SEO extends Model
{
    use HasFactory;

    protected $fillable = ['language_id', 'meta_keyword_home', 'meta_description_home', 'meta_keyword_menu', 'meta_description_menu', 'meta_keyword_item', 'meta_description_item', 'meta_keyword_about_us', 'meta_description_about_us', 'meta_keyword_career', 'meta_description_career', 'meta_keyword_team_member', 'meta_description_team_member', 'meta_keyword_gallery', 'meta_description_gallery', 'meta_keyword_faq', 'meta_description_faq', 'meta_keyword_blog', 'meta_description_blog', 'meta_keyword_contact', 'meta_description_contact', 'meta_keyword_reservation', 'meta_description_reservation', 'meta_keyword_cart', 'meta_description_cart', 'meta_keyword_checkout', 'meta_description_checkout', 'meta_keyword_login', 'meta_description_login', 'meta_keyword_signup', 'meta_description_signup', 'meta_keyword_forget_password', 'meta_description_forget_password'];

    public function seoLang()
    {
        return $this->belongsTo(Language::class);
    }
}
