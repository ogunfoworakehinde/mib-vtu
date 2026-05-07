package com.mibvtu.webapp;

import android.app.Activity;
import android.content.Intent;
import android.os.Bundle;
import android.os.Handler;
import android.webkit.WebView;
import android.webkit.WebViewClient;
import android.webkit.CookieManager;

public class SplashActivity extends Activity {
    private boolean pageLoaded = false;
    private boolean delayDone = false;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_splash);

        // Pre‑warm the WebView cache in the background
        WebView preloadWebView = new WebView(this);
        preloadWebView.getSettings().setJavaScriptEnabled(true);
        preloadWebView.getSettings().setCacheMode(android.webkit.WebSettings.LOAD_DEFAULT);
        preloadWebView.setWebViewClient(new WebViewClient() {
            @Override
            public void onPageFinished(WebView view, String url) {
                pageLoaded = true;
                tryToFinish();
            }
        });
        preloadWebView.loadUrl("https://mib-vtu-production.up.railway.app");

        // Minimum splash time 1.5 s
        new Handler().postDelayed(() -> {
            delayDone = true;
            tryToFinish();
        }, 1500);
    }

    private void tryToFinish() {
        if (pageLoaded && delayDone) {
            startActivity(new Intent(SplashActivity.this, MainActivity.class));
            finish();
        }
    }
}
