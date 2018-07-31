<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Exceptions\ValidationException;
use Illuminate\Http\Request;
use App\Http\Controllers\NotificationsController as Notification;
use Twilio\Rest\Client;

/**
 * Validate with teh given data
 *
 * @param  array $data
 * @param  array $rules
 * @param bool $softValidation using "sometimes"
 *
 * @return \Illuminate\Validation\Validator|void
 * @throws \App\Exceptions\ValidationException
 */
function validate(array $data, array $rules, $softValidation = false, $customAttributes = [])
{
//    if ($softValidation) {
//        array_walk($rules, function (&$value, $key) {
//            $value = $value;
//        });
//    }

    dd($rules);
    $validator = Validator::make($data, $rules, [], $customAttributes);
//    dd($validator);

    if ($validator->fails()) {
        throw new App\Exceptions\ValidationException(implode(', ', $validator->messages()->all()));
    }

    return $validator;
}

/**
 * @param $options
 * @param $selected
 * @return string
 */
function selectBoxOptionsBuilder($options, $selected)
{
//    dd($options .'');
    $isMultiLevel = count(array_filter($options, 'is_array')) > 0;

    $output = '';
    foreach ($options as $key => $value) {
        if ($isMultiLevel) {
            if (!is_array($value)) {
                $output .= "<option value=\"$key\">$value</option>";
                continue;
            }

            $output .= "<optgroup label=\"$key\">";

            foreach ($value as $subKey => $subValue) {
                if (is_array($selected)) {
                    $selectionMarkup = in_array($subKey, $selected) ? 'selected="selected"' : '';
                } else {
                    $selectionMarkup = $selected != '' && $selected == $subKey ? 'selected="selected"' : '';
                }

                $output .= "<option $selectionMarkup value=\"$subKey\">$subValue</option>";
            }

            $output .= "</optgroup>";

        } else {
            if (is_array($selected)) {
                $selectionMarkup = in_array($key, $selected) ? 'selected="selected"' : '';
            } else {
                $selectionMarkup = $selected != '' && $selected == $key ? 'selected="selected"' : '';
            }

            $output .= "<option $selectionMarkup value=\"$key\">$value</option>";
        }

    }

    return $output;
}

function selectBoxOptionsBuilder_parent($options, $selected, $brands = '')
{
    $isMultiLevel = count(array_filter($options, 'is_array')) > 0;

    $output = '';
    foreach ($options as $key => $value) {

        if ($isMultiLevel) {
            if (!is_array($value)) {

                $output .= "<option value=\"$key\">$value </option>";
                continue;
            }

            $output .= "<optgroup label=\"$key\">";

            foreach ($value as $subKey => $subValue) {

                if (is_array($selected)) {
                    $selectionMarkup = in_array($subKey, $selected) ? 'selected="selected"' : '';
                } else {
                    $selectionMarkup = $selected != '' && $selected == $subKey ? 'selected="selected"' : '';
                }

                $output .= "<option $selectionMarkup value=\"$subKey\">$subValue</option>";
            }

            $output .= "</optgroup>";

        } else {
            if (is_array($selected)) {
                $selectionMarkup = in_array($key, $selected) ? 'selected="selected"' : '';
            } else {
                $selectionMarkup = $selected != '' && $selected == $key ? 'selected="selected"' : '';
            }
//                foreach ($brands as $brand) {
//                    if ($brand)
//                    $brand_id = \App\Category::$BRANDS[$brand->brand];

            $output .= "<option $selectionMarkup value=\"$key\">$value $brands</option>";
            //  }
        }

    }

    return $output;
}

function startQueryLog()
{
    DB::enableQueryLog();
}

function getQueryLog()
{
    return DB::getQueryLog();
}

/**
 * @param $parentsCollection
 * @param $relationshipName
 * @param string $parentNameField
 * @param string $childIdField
 * @param string $childNameField
 * @return array
 */
function groupedSelectBoxArrayBuilder($parentsCollection, $relationshipName, $parentNameField = 'name', $childIdField = 'id', $childNameField = 'name')
{
    $output = [];

    foreach ($parentsCollection as $parent) {
        foreach ($parent->{$relationshipName} as $child) {
            $output[$parent->{$parentNameField}][$child->{$childIdField}] = $child->{$childNameField};
        }
    }

    return $output;
}

function groupedSelectBoxArrayBuilder_products($brands, $parentsCollection, $relationshipName, $parentNameField = 'name', $childIdField = 'id', $childNameField = 'name')
{
    $output = [];

    foreach ($brands as $brand_key => $brand_value) {
        foreach ($parentsCollection as $parent) {
            foreach ($parent->{$relationshipName} as $child) {
                if ($brand_key == $parent->brand) {
                    $output[$brand_value . ' - ' . $parent->{$parentNameField}][$child->{$childIdField}] = $child->{$childNameField};
                }
            }
        }
    }

    return $output;
}

function groupedSelectBoxArrayBuilder_brands($brands, $cats, $childIdField = 'id', $childNameField = 'name')
{
    $output = [];

    foreach ($brands as $key => $value) {
        foreach ($cats as $cat) {
            if ($key == $cat->brand) {
                $output[$value][$cat->{$childIdField}] = $cat->{$childNameField};
            }
        }
    }

    return $output;
}

function ValidateToken($Token)
{
    $Result = $this->DecodeToken($Token);
    if ($Result == 0) {
        return 0;
    } else {
        $user_token = substr($Token, 0, strpos($Token, "d+"));
        $user = \App\SebarUser::where('sebar_user_id', '=', $Result)->first();
        $user_email = $user->sebar_user_email;
        $UserToken = $Result . $user_email;
        if (Hash::check($UserToken, $user_token)) {
            return $Result;
        } else {
            return 0;
        }
    }
}

function DecodeText($Text)
{
    if ($Text != null) {
        $textToEncrypt = $Text;
        $encryptionMethod = "AES-128-CTR";  // AES is used by the U.S. gov't to encrypt top secret documents.
        $secretHash = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZHARBR";
        $iv = substr(hash('sha256', $secretHash), 0, 16);
        $decryptedMessage = openssl_decrypt($textToEncrypt, $encryptionMethod, $secretHash, $options = 0, $iv);
        return $UserEmailEnc = $decryptedMessage;
    }
}

function EncodeText($Text)
{
    if ($Text != null) {
        /* encrypt user id */
        $encryptionMethod = "AES-128-CTR";  // AES is used by the U.S. gov't to encrypt top secret documents.
        $secretHash = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZHARBR";
        $iv = substr(hash('sha256', $secretHash), 0, 16);
        $ENUserId = openssl_encrypt($Text, $encryptionMethod, $secretHash, $options = 0, $iv);
        return $Text = $ENUserId;
    }
}

function DecodeToken($Token)
{
    if ($Token != null) {
        $AllEnc = substr($Token, strpos($Token, "+") + 1);
        $ENUserId = substr($AllEnc, 0, strpos($AllEnc, "VT"));
        $ENUserRand = substr($AllEnc, strpos($AllEnc, "VT+") + 3);
        $VerifyToken = \App\SebarUser::where('verify_token', '=', $ENUserRand)->first();
        if ($VerifyToken === null) {
            return 0;
        }
        $textToEncrypt = $ENUserId;
        $encryptionMethod = "AES-256-CTR";  // AES is used by the U.S. gov't to encrypt top secret documents.
        $secretHash = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZHARBR";
        $iv = substr(hash('sha256', $secretHash), 0, 16);
        $decryptedMessage = openssl_decrypt($textToEncrypt, $encryptionMethod, $secretHash, $options = 0, $iv);
        $UserId = $decryptedMessage;
        $user_token = substr($Token, 0, strpos($Token, "d+"));
        $user = \App\SebarUser::where('sebar_user_id', '=', $UserId)->first();
        if ($user != null) {
            return $UserId;
        }
    }
    return 0;
}

function EncodeToken($UserId, $UserEmail)
{
    if ($UserId != null && $UserEmail != null) {
        /* encrypt user id */
        $encryptionMethod = "AES-256-CTR";  // AES is used by the U.S. gov't to encrypt top secret documents.
        $secretHash = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZHARBR";
        $iv = substr(hash('sha256', $secretHash), 0, 16);
        $ENUserId = openssl_encrypt($UserId, $encryptionMethod, $secretHash, $options = 0, $iv);
        $UserToken = $UserId . $UserEmail;
        $HashUserToken = Hash::make($UserToken);
        $Rand = rand(0, 10000000);
        $textToEncrypt = $Rand;
        $encryptionMethod = "AES-256-CTR";  // AES is used by the U.S. gov't to encrypt top secret documents.
        $secretHash = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZHARBR";
        $iv = substr(hash('sha256', $secretHash), 0, 16);
        $EncryptedMessage = openssl_encrypt($textToEncrypt, $encryptionMethod, $secretHash, $options = 0, $iv);
        $VerifyToken = \App\SebarUser::where('sebar_user_id', '=', $UserId)->update(['verify_token' => $EncryptedMessage]);
        $Token = $HashUserToken . 'd+' . $ENUserId . 'VT+' . $EncryptedMessage;
        return $Token;
    }
    return "";
}

function TransformToNotNull($output)
{
    foreach ($output as $key => $Value) {
        if ($Value === null) {
            $output[$key] = "";
        }
    }
    return $output;
}

function CheckIfNull($input)
{
    if ($input === null) {
        $status = 1;
        return \Response::json(['status' => $status, 'errors' => 'Sorry Data not found'], 401);
    }
}

function UploadUsingBase64($input)
{
    $image = base64_decode($input);
    $f = finfo_open();
    $fileMimeType = finfo_buffer($f, $image, FILEINFO_MIME_TYPE);
    if ($fileMimeType == 'image/png' || $fileMimeType == 'image/jpeg' || $fileMimeType == 'image/jpeg') {
        $extension = substr($fileMimeType, strpos($fileMimeType, "/") + 1);
        $NewName = str_random(10) . rand(0, 10);
        file_put_contents(public_path(env('UPLOAD_PATH')) . '/' . $NewName . '.' . $extension, $image);
        return $NewName . '.' . $extension;
    }
    return redirect()->back()->with('error', 'extension is not acceptable please upload png or jpg files');
}

function UploadUsingMaltyPart($input)
{
    $image = $input;
    $f = finfo_open();
    $fileMimeType = finfo_buffer($f, $image, FILEINFO_MIME_TYPE);
    if ($fileMimeType == 'image/png' || $fileMimeType == 'image/jpeg' || $fileMimeType == 'image/jpeg') {
        $extension = $image->getClientOriginalExtension();
        $NewName = str_random(10) . rand(0, 10);
        file_put_contents(public_path(env('UPLOAD_PATH')) . '/' . $NewName . $extension, $image);
        return $NewName . $extension;
    }
    return redirect()->back()->with('error', 'extension is not acceptable please upload png or jpg files');
}

function UploadPDF($file)
{
    $fileMimeType = File::mimeType($file);
    if($fileMimeType == 'application/pdf'){
        $extension = $file->getClientOriginalExtension();
        $NewName = str_random(10).rand(0,10).'.'.$extension;
        $file->move(public_path(env('UPLOAD_PATH')), $NewName);
        return $NewName;
    }
    return '';
}


function GenerateGIDNumber($len = 15)
{

    $alpha = range('A', 'Z');
    srand((double)microtime() * 1000000);

    $i = 0;
    $GIDNumber = "";

    while (strlen($GIDNumber) < $len) {
        $GIDNumber .= ($i == 0 || $i % 3 == 0) ? $alpha[mt_rand(0, 25)] : rand(0, 9);
        $i++;
    }

    return ($GIDNumber);

}

function createLog($action, $model, $adminId, $itemId)
{
    $data = [
        'action' => $action,
        'model' => $model,
        'user_id' => $adminId,
        'item_id' => $itemId,
    ];
    Log::create($data);
}

function MyResponse($Status, $Data = null, $Massage, $ResponseStatus = 200)
{
    if ($Data !== null) {
        return response()->json(['status' => $Status, 'data' => $Data, 'message' => $Massage], $ResponseStatus);
    }
    return response()->json(['status' => $Status, 'data' => (object)[], 'message' => $Massage], $ResponseStatus);
}


function GenerateTandaCode()
{
    $charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890!@#$&";
    return substr(str_shuffle($charset), 0, 5);

}

function sendMessageUsingTwilio($phone, $message, $code)
{
    $keySid = 'AC0bc315f5f80f966176e10aa90492d09f';
    $keySecret = '07dc0edf5fef9214fe933a3aedf40ed4';
    $accountSid = 'ACyouraccountsid';
    $Client = New Client($keySid, $keySecret);
    $SendSms = New Notification($Client);
    $SendSms->send($phone, $message . $code);
}

function PrivacyGroups()
{
    return [
        0 => 'LastOnlineTime',
        1 => 'ReadConfirmation',
        2 => 'ProfilePicture',
        3 => 'Online',
        4 => 'Offline',
        5 => 'Invisible',
        6 => 'Busy',
        7 => 'WeekendMode',
    ];
}