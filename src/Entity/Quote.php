<?php

class Quote
{
    const SUMMARY = '[quote:summary]';
    const SUMMARY_HTML = '[quote:summary_html]';
    const DESTINATION_NAME = '[quote:destination_name]';
    const DESTINATION_LINK = '[quote:destination_link]';
    public $id;
    public $siteId;
    public $destinationId;
    public $dateQuoted;

    public function __construct($id, $siteId, $destinationId, $dateQuoted)
    {
        $this->id = $id;
        $this->siteId = $siteId;
        $this->destinationId = $destinationId;
        $this->dateQuoted = $dateQuoted;
    }

    // todo:  Create decorators ex: HtmlQuote::render, PlainTextQuote::render
    public static function renderHtml(Quote $quote)
    {
        return '<p>' . $quote->id . '</p>';
    }

    public static function renderText(Quote $quote)
    {
        return (string) $quote->id;
    }
}