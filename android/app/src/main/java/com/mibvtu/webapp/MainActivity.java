package com.mibvtu.webapp;

import android.app.Activity;
import android.content.Context;
import android.net.ConnectivityManager;
import android.net.Network;
import android.net.NetworkCapabilities;
import android.net.NetworkRequest;
import android.os.Bundle;
import android.os.Handler;
import android.view.View;
import android.webkit.WebSettings;
import android.webkit.WebView;
import android.webkit.WebViewClient;
import android.widget.RelativeLayout;

public class MainActivity extends Activity {
    private WebView webView;
    private View splashView;
    private ConnectivityManager connectivityManager;
    private ConnectivityManager.NetworkCallback networkCallback;

    private final String LIVE_URL = "https://mib-vtu-production.up.railway.app";
    private final String OFFLINE_PAGE = "file:///android_asset/offline.html";
    private boolean isOfflinePageDisplayed = false;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);

        // Create a container that holds both the splash and the WebView
        RelativeLayout root = new RelativeLayout(this);
        
        // Splash screen (the same XML layout you already have)
        splashView = getLayoutInflater().inflate(R.layout.activity_splash, root, false);
        root.addView(splashView);

        // WebView (hidden until page loads)
        webView = new WebView(this);
        webView.setVisibility(View.INVISIBLE);
        WebSettings settings = webView.getSettings();
        settings.setJavaScriptEnabled(true);
        settings.setDomStorageEnabled(true);
        settings.setCacheMode(WebSettings.LOAD_CACHE_ELSE_NETWORK);
        settings.setAllowFileAccess(true);

        webView.setWebViewClient(new WebViewClient() {
            @Override
            public void onReceivedError(WebView view, int errorCode,
                                        String description, String failingUrl) {
                if (failingUrl != null && failingUrl.startsWith("https://mib-vtu")) {
                    webView.loadUrl(OFFLINE_PAGE);
                    isOfflinePageDisplayed = true;
                    // When offline page is displayed, remove splash
                    splashView.setVisibility(View.GONE);
                    webView.setVisibility(View.VISIBLE);
                }
            }

            @Override
            public void onPageFinished(WebView view, String url) {
                // Once the live site (or offline page) finishes loading, hide splash
                if (url.equals(LIVE_URL) || url.equals(OFFLINE_PAGE)) {
                    splashView.setVisibility(View.GONE);
                    webView.setVisibility(View.VISIBLE);
                    if (url.equals(LIVE_URL)) {
                        isOfflinePageDisplayed = false;
                    }
                }
            }
        });

        root.addView(webView);
        setContentView(root);

        // Network callback (unchanged logic)
        connectivityManager = (ConnectivityManager) getSystemService(Context.CONNECTIVITY_SERVICE);
        NetworkRequest networkRequest = new NetworkRequest.Builder()
                .addCapability(NetworkCapabilities.NET_CAPABILITY_INTERNET)
                .build();

        networkCallback = new ConnectivityManager.NetworkCallback() {
            @Override
            public void onLost(Network network) {
                webView.post(() -> {
                    webView.loadUrl(OFFLINE_PAGE);
                    isOfflinePageDisplayed = true;
                });
            }

            @Override
            public void onAvailable(Network network) {
                webView.post(() -> {
                    if (isOfflinePageDisplayed) {
                        webView.loadUrl(LIVE_URL);
                        isOfflinePageDisplayed = false;
                    }
                });
            }
        };

        connectivityManager.registerNetworkCallback(networkRequest, networkCallback);

        // Start loading the live site
        webView.loadUrl(LIVE_URL);
        
        // Fallback: if the page doesn't load within 5 seconds, show splash anyway (maybe offline)
        new Handler().postDelayed(() -> {
            if (webView.getVisibility() != View.VISIBLE) {
                splashView.setVisibility(View.GONE);
                webView.setVisibility(View.VISIBLE);
            }
        }, 5000);
    }

    @Override
    protected void onDestroy() {
        super.onDestroy();
        if (connectivityManager != null && networkCallback != null) {
            connectivityManager.unregisterNetworkCallback(networkCallback);
        }
    }
}
