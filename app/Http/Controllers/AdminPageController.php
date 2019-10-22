<?php

namespace App\Http\Controllers;

use App\ArticleCategory;
use App\User;
use Illuminate\Http\Request;

class AdminPageController extends Controller
{
    /**
     * Admin home page controller
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index() {
        return view('admin.index');
    }

    public function ads() {
        return view('admin.empty');
    }

    public function ad() {
        return view('admin.empty');
    }

    public function adCategories() {
        return view('admin.empty');
    }

    public function adCategory() {
        return view('admin.empty');
    }

    public function adTags() {
        return view('admin.empty');
    }

    public function adTag() {
        return view('admin.empty');
    }

    public function pages() {
        return view('admin.empty');
    }

    public function page() {
        return view('admin.empty');
    }

    public function articles () {
        return view('admin.articles.list');
    }

    public function article() {
        return view('admin.articles.article');
    }

    public function countries() {
        return view('admin.empty');
    }

    public function country() {
        return view('admin.empty');
    }

    public function cities() {
        return view('admin.empty');
    }

    public function city() {
        return view('admin.empty');
    }

    public function adSenseBlocks() {
        return view('admin.settings.list');
    }

    public function adSenseBlock() {
        return view('admin.empty');
    }

}
