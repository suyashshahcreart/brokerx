<?php

namespace App\Services;

use App\Models\QR;
use App\Models\Tour;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

class TourService
{
    public function syncTourFieldsFromJson(Tour $tour, array $finalJson, array $diffJson = [], bool $forceSync = false): void
    {
        \Log::info("Tour Details sync started for tour_id={$tour->id}, force_sync={$forceSync}");
        $userInfo = $finalJson['userInfo'] ?? [];
        //  sidebar nodes
        if ($forceSync || Arr::has($diffJson, 'nodes')) {
            $tour->sidebar_node = $finalJson['nodes'] ?? [];
        }
        // user related fields
        if ($forceSync || Arr::has($diffJson, 'userInfo.userName')) {
            $tour->contact_user_name = $userInfo['userName'] ?? null;
        }
        if ($forceSync || Arr::has($diffJson, 'userInfo.googleLocation')) {
            $tour->contact_google_location = $userInfo['googleLocation'] ?? null;
        }
        if ($forceSync || Arr::has($diffJson, 'userInfo.website')) {
            $tour->contact_website = $userInfo['website'] ?? null;
        }
        if ($forceSync || Arr::has($diffJson, 'userInfo.email')) {
            $tour->contact_email = $userInfo['email'] ?? null;
        }
        if ($forceSync || Arr::has($diffJson, 'userInfo.phoneNumber')) {
            $tour->contact_phone_no = $userInfo['phoneNumber'] ?? null;
        }
        if ($forceSync || Arr::has($diffJson, 'userInfo.whatsAppNumber')) {
            $tour->contact_whatsapp_no = $userInfo['whatsAppNumber'] ?? null;
        }

        if ($forceSync || Arr::has($diffJson, 'userInfo.showUserName')) {
            $tour->show_contact_user_name = $userInfo['showUserName'] ?? false;
        }
        if ($forceSync || Arr::has($diffJson, 'userInfo.showGoogleLocation')) {
            $tour->show_contact_google_location = $userInfo['showGoogleLocation'] ?? false;
        }
        if ($forceSync || Arr::has($diffJson, 'userInfo.showEmail')) {
            $tour->show_contact_email = $userInfo['showEmail'] ?? false;
        }
        if ($forceSync || Arr::has($diffJson, 'userInfo.showWebsite')) {
            $tour->show_contact_website = $userInfo['showWebsite'] ?? false;
        }
        if ($forceSync || Arr::has($diffJson, 'userInfo.showPhoneNumber')) {
            $tour->show_contact_phone_no = $userInfo['showPhoneNumber'] ?? false;
        }
        if ($forceSync || Arr::has($diffJson, 'userInfo.showWhatsAppNumber')) {
            $tour->show_contact_whatsapp_no = $userInfo['showWhatsAppNumber'] ?? false;
        }

        // document show and auth fields
        if ($forceSync || Arr::has($diffJson, 'userInfo.showDocumentUrl')) {
            $tour->show_document_url = $userInfo['showDocumentUrl'] ?? false;
        }
        if ($forceSync || Arr::has($diffJson, 'userInfo.showDocumentUr2')) {
            $tour->show_document_url2 = $userInfo['showDocumentUrl2'] ?? false;
        }
        if ($forceSync || Arr::has($diffJson, 'userInfo.documentAuthRequired')) {
            $tour->document_auth_required = $userInfo['documentAuthRequired'] ?? false;
        }

        //  user info -> user details 
        if ($forceSync || Arr::has($diffJson, 'userInfo.showUserDetailsButton')) {
            $tour->show_user_details_button = $userInfo['showUserDetailsButton'] ?? false;
        }
        if ($forceSync || Arr::has($diffJson, 'userInfo.userDetailsButtonIcon')) {
            $tour->user_details_button_icon = $userInfo['userDetailsButtonIcon'] ?? null;
        }
        if ($forceSync || Arr::has($diffJson, 'userInfo.userDetailsButtonTooltip')) {
            $tour->user_details_button_tooltip = $userInfo['userDetailsButtonTooltip'] ?? null;
        }
        if ($forceSync || Arr::has($diffJson, 'userInfo.userDetails')) {
            $tour->user_details = $userInfo['userDetails'] ?? null;
        }


        /* Attchment file sync */
        if ($forceSync || Arr::has($diffJson, 'userInfo.documentAuthType')) {
            $tour->attachment_file = [
                [
                    "documentType" => $userInfo['documentType'] ?? 'image',
                    "documentTooltip" => $userInfo['documentTooltip'] ?? 'Document_1',
                    "documentAction" => $userInfo['documentAction'] ?? 'downloard',
                    "documentUrl" => $userInfo['documentUrl'] ?? 'null'
                ],
                [
                    "documentType" => $userInfo['documentType2'] ?? 'image',
                    "documentTooltip" => $userInfo['documentTooltip2'] ?? 'Document_2',
                    "documentAction" => $userInfo['documentAction'] ?? 'downloard',
                    "documentUrl" => $userInfo['documentUrl'] ?? 'null'
                ]
            ] ?? null;
        }


        $localeConfig = $finalJson['localeConfig'] ?? [];
        if ($forceSync || Arr::has($diffJson, 'localeConfig.defaultLanguage')) {
            $tour->default_language = $localeConfig['defaultLanguage'] ?? 'en';
        }
        if ($forceSync || Arr::has($diffJson, 'localeConfig.enabledLanguages')) {
            $tour->enable_language = $localeConfig['enabledLanguages'] ?? ['en', 'hi'];
        }

        $loaderConfig = $finalJson['loaderConfig'] ?? [];
        if ($forceSync || Arr::has($diffJson, 'loaderConfig')) {
            $tour->loader_text = $loaderConfig['loadingText'] ?? "It's Prop Pik, It's Real";
            $tour->overlay_bg_color = $loaderConfig['overlayBackgroundColor'] ?? '#3949AB';
        }

        if ($forceSync || Arr::has($diffJson, 'loaderConfig.spinnerGradientColor1') || Arr::has($diffJson, 'loaderConfig.spinnerGradientColor2') || Arr::has($diffJson, 'loaderConfig.spinnerGradientColor3')) {
            $tour->spinner_color = [
                $loaderConfig['spinnerGradientColor1'] ?? '#FF5F5F',
                $loaderConfig['spinnerGradientColor2'] ?? '#FF5F5F',
                $loaderConfig['spinnerGradientColor3'] ?? '#FF5F5F',
            ];
        }

        if ($forceSync || Arr::has($diffJson, 'loaderConfig.textGradientColor1') || Arr::has($diffJson, 'loaderConfig.textGradientColor2') || Arr::has($diffJson, 'loaderConfig.textGradientColor3')) {
            $tour->loader_color = [
                $loaderConfig['textGradientColor1'] ?? '#FF5F5F',
                $loaderConfig['textGradientColor2'] ?? '#FF5F5F',
                $loaderConfig['textGradientColor3'] ?? '#FF5F5F',
            ];
        }

        $sidebarConfig = $finalJson['sidebarConfig'] ?? [];
        $footerButton = $sidebarConfig['footerButton'] ?? [];

        if ($forceSync || Arr::has($diffJson, 'sidebarConfig.logo')) {
            $bookingCode = QR::where('booking_id', $tour->booking_id ?? null)->value('code');
            $logo = $sidebarConfig['logo'] ?? null;
            $path = $bookingCode && $logo ? "tours/$bookingCode/$logo" : null;
            $tour->sidebar_logo = $path ? Storage::disk('s3')->url($path) : null;
        }

        if ($forceSync || Arr::has($diffJson, 'sidebarConfig.footerButton')) {
            $tour->sidebar_footer_text = $footerButton['text']['en'] ?? 'Designe By PROP PIK';
            $tour->sidebar_footer_link = $footerButton['link'] ?? null;
        }

        if ($forceSync || Arr::has($diffJson, 'sidebarConfig.sidebarTag')) {
            $sidebarTag = $sidebarConfig['sidebarTag'] ?? [];
            $tour->sidebar_tag_text = $sidebarTag['text'] ?? null;
            $tour->sidebar_tag_color = $sidebarTag['color'] ?? '#ffffff';
            $tour->sidebar_tag_bg_color = $sidebarTag['backgroundColor'] ?? null;
        }

        if ($forceSync || Arr::has($diffJson, 'sidebarLinks')) {
            $tour->sidebar_links = $finalJson['sidebarLinks'] ?? [];
        }

        // bottom mark fields
        $bottomMarker = $finalJson['bottomMarker'] ?? [];
        if ($forceSync || Arr::has($diffJson, 'bottomMarker.topImage')) {
            $bookingCode = QR::where('booking_id', $tour->booking_id ?? null)->value('code');
            $logo = $bottomMarker['topImage'] ?? null;
            $path = $bookingCode && $logo ? "tours/$bookingCode/$logo" : null;
            $tour->footer_logo = Storage::disk('s3')->url($path) ?? null;
        }
        if ($forceSync || Arr::has($diffJson, 'bottomMarker.topTitle')) {
            $tour->footer_title = $bottomMarker['topTitle'] ?? null;
        }
        if ($forceSync || Arr::has($diffJson, 'bottomMarker.topSubTitle')) {
            $tour->footer_subtitle = $bottomMarker['topSubTitle'] ?? null;
        }
        if ($forceSync || Arr::has($diffJson, 'bottomMarker.topDescription')) {
            $tour->footer_decription = $bottomMarker['topDescription'] ?? null;
        }
        if ($forceSync || Arr::has($diffJson, 'bottomMarker.propertyName')) {
            $tour->bottommark_property_name = $bottomMarker['propertyName'] ?? null;
        }
        if ($forceSync || Arr::has($diffJson, 'bottomMarker.roomType')) {
            $tour->bottommark_room_type = $bottomMarker['roomType'] ?? null;
        }
        if ($forceSync || Arr::has($diffJson, 'bottomMarker.dimensions')) {
            $tour->bottommark_dimensions = $bottomMarker['dimensions'] ?? null;
        }
        if ($forceSync || Arr::has($diffJson, 'bottomMarker.contactNumber')) {
            $tour->footer_mobile = $bottomMarker['contactNumber'] ?? null;
        }
        if ($forceSync || Arr::has($diffJson, 'bottomMarker.contactEmail')) {
            $tour->footer_email = $bottomMarker['contactEmail'] ?? null;
        }
        // Bookmark fields add
        $bookmark = $finalJson['bookmark'] ?? [];
        if ($forceSync || Arr::has($diffJson, 'bookmark.showBookmarkButton')) {
            $bookmarkTitle = $bookmark['bookmarkTitle'] ?? null;
            if (is_array($bookmarkTitle)) {
                $bookmarkTitle = array_filter($bookmarkTitle, static fn($value) => is_string($value) && trim($value) !== '');
                $tour->bookmark_title = empty($bookmarkTitle)
                    ? null
                    : json_encode($bookmarkTitle, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            } elseif (is_string($bookmarkTitle) && trim($bookmarkTitle) !== '') {
                $tour->bookmark_title = json_encode(['en' => $bookmarkTitle], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            } else {
                $tour->bookmark_title = null;
            }
            $tour->bookmark_ribbon_background_color = $bookmark['ribbonBackgroundColor'] ?? null;
            $tour->bookmark_ribbon_text_color = $bookmark['ribbonTextColor'] ?? null;
            $tour->bookmark_show_on_tour_load = $bookmark['showOnTourLoad'] ?? 0;
            $tour->bookmark_show_on_tour_load_delay_ms = $bookmark['showOnTourLoadDelayMs'] ?? 0;
            $tour->bookmark_action = $bookmark['action'] ?? null;
            $tour->bookmark_modal_title = $bookmark['modalTitle'] ?? null;
            $tour->bookmark_modal_description = $bookmark['modalDescription'] ?? null;
            $tour->bookmark_info_modal_footer_button_title = $bookmark['infoModalFooterButtonTitle'] ?? null;
            $tour->bookmark_info_modal_footer_button_link = $bookmark['infoModalFooterButtonLink'] ?? null;
            $tour->bookmark_info_modal_footer_text = $bookmark['infoModalFooterText'] ?? null;
            $tour->bookmark_open_link_url = $bookmark['openLinkUrl'] ?? null;
            $tour->bookmark_document_url = $bookmark['documentUrl'] ?? null;
            $tour->bookmark_video_url = $bookmark['videoUrl'] ?? null;
            $bookmarkImageUrls = $bookmark['imageUrls'] ?? [];
            if (!is_array($bookmarkImageUrls)) {
                $bookmarkImageUrls = !empty($bookmarkImageUrls) ? [$bookmarkImageUrls] : [];
            }
            $tour->bookmark_images_url = $bookmarkImageUrls;
            $tour->bookmark_image_url = !empty($bookmarkImageUrls)
                ? $bookmarkImageUrls[0]
                : ($bookmark['imageUrl'] ?? null);
        }
        /* User Star Rating sync function */
        if($forceSync || Arr::has($diffJson, 'bottomMarker.userStars')) {
            $tour->user_star = $bottomMarker['userStars'] ?? null;
        }
    }
}