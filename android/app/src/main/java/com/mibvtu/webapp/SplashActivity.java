package com.mibvtu.webapp;

import android.app.Activity;
import android.content.Intent;
import android.os.Bundle;
import android.os.Handler;
import android.webkit.WebView;
import android.webkit.WebViewClient;

public class SplashActivity extends Activity {
    private boolean pageLoaded = false;
    private boolean delayDone = false;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_splash);

        // Preload the live site in a hidden WebView to warm the cache
        WebView preloadWebView = new WebView(this);
        preloadWebView.getSettings().setJavaScriptEnabled(true);
        preloadWebView.getSettings().setCacheMode(android.webkit.WebSettings.LOAD_CACHE_ELSE_NETWORK);
        preloadWebView.setWebViewClient(new WebViewClient() {
            @Override
            public void onPageFinished(WebView view, String url) {
                pageLoaded = true;
                tryToFinish();
            }
        });
        preloadWebView.loadUrl("https://mib-vtu-production.up.railway.app");

        // Minimum splash time 1.5 seconds
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
