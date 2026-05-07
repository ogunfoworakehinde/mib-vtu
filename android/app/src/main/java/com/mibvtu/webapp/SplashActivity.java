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
    private boolean finished = false;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_splash);

        // Pre‑warm the WebView cache
        WebView preloadWebView = new WebView(this);
        preloadWebView.getSettings().setJavaScriptEnabled(true);
        preloadWebView.getSettings().setCacheMode(android.webkit.WebSettings.LOAD_DEFAULT);
        preloadWebView.setWebViewClient(new WebViewClient() {
            @Override
            public void onPageFinished(WebView view, String url) {
                pageLoaded = true;
                tryToFinish();
            }

            @Override
            public void onReceivedError(WebView view, int errorCode,
                                        String description, String failingUrl) {
                // Stop waiting — the network is not available
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

        // HARD TIMEOUT: after 7 seconds, force transition regardless
        new Handler().postDelayed(() -> {
            if (!finished) {
                pageLoaded = true;
                delayDone = true;
                tryToFinish();
            }
        }, 7000);
    }

    private void tryToFinish() {
        if (finished) return;
        if (pageLoaded && delayDone) {
            finished = true;
            startActivity(new Intent(SplashActivity.this, MainActivity.class));
            finish();
        }
    }
}
