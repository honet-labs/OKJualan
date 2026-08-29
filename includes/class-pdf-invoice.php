<?php
if (!defined('ABSPATH')) { exit; }

class OKJ_PDF_Invoice {
    private function hex_to_rgb($hex) {
        $hex = str_replace('#', '', (string)$hex);
        if (strlen($hex) === 3) {
            $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1)) / 255;
            $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1)) / 255;
            $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1)) / 255;
        } else {
            $r = hexdec(substr($hex, 0, 2)) / 255;
            $g = hexdec(substr($hex, 2, 2)) / 255;
            $b = hexdec(substr($hex, 4, 2)) / 255;
        }
        return [$r, $g, $b];
    }

    private function sanitize_text($t) {
        $t = (string)$t;
        $t = html_entity_decode($t, ENT_QUOTES, 'UTF-8');
        $t = str_replace([
            "\xC2\xA0", "\xE2\x80\x80", "\xE2\x80\x81", "\xE2\x80\x82", "\xE2\x80\x83",
            "\xE2\x80\x84", "\xE2\x80\x85", "\xE2\x80\x86", "\xE2\x80\x87",
            "\xE2\x80\x88", "\xE2\x80\x89", "\xE2\x80\x8A", "\xE2\x80\x8B",
        ], ' ', $t);
        $t = str_replace(["\xE2\x80\x93", "\xE2\x80\x94", "\xE2\x88\x92"], '-', $t);
        $t = str_replace("\xE2\x80\xA6", '...', $t);
        $t = preg_replace('/\s+/u', ' ', $t);
        $t = trim($t);

        if (function_exists('iconv')) {
            $conv = @iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $t);
            if ($conv !== false && $conv !== '') {
                $t = $conv;
            }
        } else {
            $t = preg_replace('/[^\x20-\x7E]/', '', $t);
        }
        return $t;
    }

    private function escape_text($t) {
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], (string)$t);
    }

    private function utf8_strlen($text) {
        if (function_exists('mb_strlen')) return (int)mb_strlen($text, 'UTF-8');
        return (int)preg_match_all('/./us', $text, $m);
    }

    private function utf8_substr($text, $start, $len) {
        if (function_exists('mb_substr')) return (string)mb_substr($text, $start, $len, 'UTF-8');
        preg_match_all('/./us', $text, $m);
        $chars = $m[0] ?? [];
        return implode('', array_slice($chars, $start, $len));
    }

    private function wrap_lines($text, $max_chars) {
        $text = html_entity_decode((string)$text, ENT_QUOTES, 'UTF-8');
        $text = preg_replace('/\s+/u', ' ', $text);
        $text = trim($text);
        if ($text === '') return [''];

        $max = max(1, (int)$max_chars);
        $words = preg_split('/\s+/u', $text);
        $lines = [];
        $line = '';

        foreach ((array)$words as $w) {
            $w = (string)$w;
            if ($w === '') continue;
            if ($line === '') {
                if ($this->utf8_strlen($w) <= $max) {
                    $line = $w;
                } else {
                    $pos = 0;
                    $len = $this->utf8_strlen($w);
                    while ($pos < $len) {
                        $lines[] = $this->utf8_substr($w, $pos, $max);
                        $pos += $max;
                    }
                    $line = '';
                }
            } else {
                $candidate = $line . ' ' . $w;
                if ($this->utf8_strlen($candidate) <= $max) {
                    $line = $candidate;
                } else {
                    $lines[] = $line;
                    $line = $w;
                }
            }
        }
        if ($line !== '') $lines[] = $line;
        return $lines;
    }

    private function build_pdf_document($content_stream) {
        $len = strlen($content_stream);
        $objects = [];
        $objects[] = "1 0 obj << /Type /Catalog /Pages 2 0 R >> endobj\n";
        $objects[] = "2 0 obj << /Type /Pages /Kids [3 0 R] /Count 1 >> endobj\n";
        $objects[] = "3 0 obj << /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 4 0 R /F2 6 0 R >> >> /Contents 5 0 R >> endobj\n";
        $objects[] = "4 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> endobj\n";
        $objects[] = "5 0 obj << /Length {$len} >> stream\n{$content_stream}endstream endobj\n";
        $objects[] = "6 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >> endobj\n";

        $pdf = "%PDF-1.4\n";
        $xref = [];
        $offset = strlen($pdf);
        foreach ($objects as $obj) {
            $xref[] = $offset;
            $pdf .= $obj;
            $offset = strlen($pdf);
        }
        $xref_pos = $offset;
        $pdf .= "xref\n0 " . (count($xref) + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";
        foreach ($xref as $o) {
            $pdf .= sprintf("%010d 00000 n \n", $o);
        }
        $pdf .= "trailer << /Size " . (count($xref) + 1) . " /Root 1 0 R >>\nstartxref\n{$xref_pos}\n%%EOF";
        return $pdf;
    }

    private function draw_text($x, $y, $text, $bold = false, $size = 10) {
        $font = $bold ? 'F2' : 'F1';
        $t = $this->escape_text($this->sanitize_text($text));
        return "BT\n/{$font} {$size} Tf\n1 0 0 1 " . (float)$x . " " . (float)$y . " Tm\n({$t}) Tj\nET\n";
    }

    public function generate_invoice($row, $settings) {
        $title = !empty($settings['pdf_invoice_title']) ? $settings['pdf_invoice_title'] : 'INVOICE';
        $id = (string)($row['id'] ?? '');
        $date = wp_date('d/m/Y');

        $customer = (string)($row['customer_name'] ?? '');
        $product = (string)($row['product_label'] ?? '');
        $purchase = (string)($row['start_date'] ?? '');
        $duration = (int)($row['duration_days'] ?? 0) . ' hari';
        $expired = (string)($row['expires_at'] ?? '');
        $price = 'Rp ' . number_format_i18n((float)($row['price'] ?? 0), 0);

        $x0 = 70;
        $maxW = 455;
        $y_title = 770;

        $content = "q\n1 w\n0 0 0 RG\n";
        $content .= $this->draw_text($x0, $y_title, strtoupper($title), true, 22);

        $p_color = !empty($settings['pdf_primary_color']) ? $settings['pdf_primary_color'] : '#1e293b';
        list($r, $g, $b) = $this->hex_to_rgb($p_color);

        $comp_name = !empty($settings['pdf_company_name']) ? $settings['pdf_company_name'] : '';
        $comp_addr = !empty($settings['pdf_company_address']) ? $settings['pdf_company_address'] : '';
        $comp_phone = !empty($settings['pdf_company_phone']) ? $settings['pdf_company_phone'] : '';

        $x_right = $x0 + 240;
        if ($comp_name) {
            $content .= $this->draw_text($x_right, $y_title, $comp_name, true, 12);
            $y_comp = $y_title - 13;
            if ($comp_addr) {
                $content .= $this->draw_text($x_right, $y_comp, $comp_addr, false, 8);
                $y_comp -= 9;
            }
            if ($comp_phone) {
                $content .= $this->draw_text($x_right, $y_comp, 'Telp/WA: ' . $comp_phone, false, 8);
            }
        }

        $content .= sprintf("q\n%.3f %.3f %.3f RG\n2 w\n%d %d m %d %d l S\nQ\n", $r, $g, $b, $x0, $y_title - 45, $x0 + $maxW, $y_title - 45);
        $content .= $this->draw_text($x0, $y_title - 22, 'Invoice ID: ' . $id, false, 10);
        $content .= $this->draw_text($x0, $y_title - 36, 'Tanggal: ' . $date, false, 10);

        $content .= $this->draw_text($x0, $y_title - 70, 'Detail Transaksi:', true, 11);

        $table_top = $y_title - 90;
        $header_h = 18;
        $font_size = 9;
        $line_h = 10;
        $pad = 4;

        $w_purchase = 70;
        $w_duration = 55;
        $w_expired  = 65;
        $w_price    = 70;

        $remaining = $maxW - ($w_purchase + $w_duration + $w_expired + $w_price);
        $w_prod = (int)round($remaining * 0.6);
        $w_cust = $remaining - $w_prod;

        $cols = [
            ['Customer', $w_cust],
            ['Produk', $w_prod],
            ['Mulai', $w_purchase],
            ['Durasi', $w_duration],
            ['Expired', $w_expired],
            ['Harga', $w_price],
        ];

        $charW = 0.55 * $font_size;
        $cust_lines = $this->wrap_lines($customer, max(1, floor(($w_cust - $pad*2) / $charW)));
        $prod_lines = $this->wrap_lines($product, max(1, floor(($w_prod - $pad*2) / $charW)));
        $max_lines = max(1, count($cust_lines), count($prod_lines));

        $row_h = max(18, ($line_h * $max_lines) + 8);
        $table_h = $header_h + $row_h;
        $table_bottom = $table_top - $table_h;

        $content .= sprintf("%d %d %d %d re S\n", $x0, $table_bottom, $maxW, $table_h);
        $content .= sprintf("%d %d m %d %d l S\n", $x0, $table_top - $header_h, $x0 + $maxW, $table_top - $header_h);

        $cx = $x0;
        foreach ($cols as $i => $c) {
            $cx += (int)$c[1];
            if ($i < count($cols) - 1) {
                $content .= sprintf("%d %d m %d %d l S\n", $cx, $table_bottom, $cx, $table_top);
            }
        }

        $tx = $x0 + $pad;
        foreach ($cols as $c) {
            $content .= $this->draw_text($tx, $table_top - 13, $c[0], true, $font_size);
            $tx += (int)$c[1];
        }

        $base_y = $table_top - $header_h - 13;
        $tx = $x0 + $pad;

        foreach ($cust_lines as $li => $line) {
            $content .= $this->draw_text($tx, $base_y - ($line_h * $li), $line, false, $font_size);
        }
        $tx += $w_cust;

        foreach ($prod_lines as $li => $line) {
            $content .= $this->draw_text($tx, $base_y - ($line_h * $li), $line, false, $font_size);
        }
        $tx += $w_prod;

        $content .= $this->draw_text($tx, $base_y, $purchase, false, $font_size);
        $tx += $w_purchase;
        $content .= $this->draw_text($tx, $base_y, $duration, false, $font_size);
        $tx += $w_duration;
        $content .= $this->draw_text($tx, $base_y, $expired, false, $font_size);
        $tx += $w_expired;
        $content .= $this->draw_text($tx, $base_y, $price, false, $font_size);

        $content .= $this->draw_text($x0, $table_bottom - 30, 'Terima Kasih atas kepercayaan Anda.', false, 10);

        $pay_details = !empty($settings['pdf_payment_details']) ? $settings['pdf_payment_details'] : '';
        if ($pay_details) {
            $y_pay = $table_bottom - 55;
            $content .= $this->draw_text($x0, $y_pay, 'INSTRUKSI PEMBAYARAN:', true, 9);
            $pay_lines = preg_split("/\r\n|\n|\r/", $pay_details);
            foreach ($pay_lines as $idx => $line) {
                $content .= $this->draw_text($x0, $y_pay - 12 - ($idx * 10), (string)$line, false, 8);
            }
        }

        $content .= $this->draw_text(350, 28, 'Dicetak: ' . wp_date('d/m/Y H:i:s'), false, 8);
        $content .= "Q\n";

        return $this->build_pdf_document($content);
    }
}
