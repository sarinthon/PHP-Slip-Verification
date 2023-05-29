### Sample code

```php
Route::post('slip', function (Request $request) {
    $slipFile = $request->file("file");
    $result = new MiniQR(QRCodeReader::qrcode2text($slipFile));

    return response([
        "result"=> $result
    ], 200);
});
```
