<?php

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

if (!function_exists('responeSuccess')) {
    function responeSuccess($data = null, $message, $statusCode = 200)
    {
        return response()->json(
            [
                'status' => true,
                'statusCode' => $statusCode,
                'message' => $message,
                'data' => $data,
            ],
            200,
        );
    }
}


if (!function_exists('sendErrorResponse')) {
    function sendErrorResponse($message, $data = null, $statusCode = 400)
    {
        return response()->json(
            [
                'status' => false,
                'statusCode' => $statusCode,
                'message' => $message,
                'data' => $data,
            ],
            $statusCode,
        );
    }
}

if (!function_exists('storeFiles')) {
    function storeFiles($folder, $file)
    {
        return Storage::disk(env('STORAGE_TYPE'))->put($folder, $file);
    }
}

function prePageLimit()
{
    return  request()->query('limit') ? request()->query('limit') : 32;
}

function getLoginToken()
{
    return str_replace("Bearer ", "", request()->header('authorization'));
}

function convertToSeconds($duration)
{
    $parts = explode(':', $duration);
    return $parts[0] * 3600 + $parts[1] * 60 + $parts[2];
}

function createDebugLogFile($path, $data = null)
{
    return Log::build(['driver' => 'single',  'path' => storage_path('logs/' . $path . '.log'),])
        ->debug("Response Log :", array($data));
}
