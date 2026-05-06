package com.mibvtu.webapp;

import android.app.Activity;
import android.content.Context;
import android.net.ConnectivityManager;
import android.net.Network;
import android.net.NetworkCapabilities;
import android.net.NetworkRequest;
import android.os.Bundle;
import android.webkit.WebSettings;
import android.webkit.WebView;
import android.webkit.WebViewClient;

public class MainActivity extends Activity {
    private WebView webView;
    private ConnectivityManager connectivityManager;
    private ConnectivityManager.NetworkCallback networkCallback;

    private final String LIVE_URL = "https://mib-vtu-production.up.railway.app";
    private final String OFFLINE_PAGE = "file:///android_asset/offline.html";
    private boolean isOfflinePageDisplayed = false;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);

        webView = new WebView(this);
        webView.getSettings().setJavaScriptEnabled(true);
        webView.getSettings().setDomStorageEnabled(true);

        webView.setWebViewClient(new WebViewClient() {
            @Override
            public void onReceivedError(WebView view, int errorCode,
                                        String description, String failingUrl) {
                // Only show offline page if the live URL fails
                if (failingUrl.startsWith("https://mib-vtu")) {
                    view.loadUrl(OFFLINE_PAGE);
                    isOfflinePageDisplayed = true;
                }
            }

            @Override
            public void onPageFinished(WebView view, String url) {
                // If we're back on the live site, reset the flag
                if (url.equals(LIVE_URL)) {
                    isOfflinePageDisplayed = false;
                }
            }
        });

        // Register network callback
        connectivityManager = (ConnectivityManager) getSystemService(Context.CONNECTIVITY_SERVICE);
        NetworkRequest networkRequest = new NetworkRequest.Builder()
                .addCapability(NetworkCapabilities.NET_CAPABILITY_INTERNET)
                .build();

        networkCallback = new ConnectivityManager.NetworkCallback() {
            @Override
            public void onLost(Network network) {
                // Internet gone – show offline page
                webView.post(() -> {
                    webView.loadUrl(OFFLINE_PAGE);
                    isOfflinePageDisplayed = true;
                });
            }

            @Override
            public void onAvailable(Network network) {
                // Internet back – reload live site if we were offline
                webView.post(() -> {
                    if (isOfflinePageDisplayed) {
                        webView.loadUrl(LIVE_URL);
                        isOfflinePageDisplayed = false;
                    }
                });
            }
        };

        connectivityManager.registerNetworkCallback(networkRequest, networkCallback);

        // Load live site initially
        webView.loadUrl(LIVE_URL);
        setContentView(webView);
    }

    @Override
    protected void onDestroy() {
        super.onDestroy();
        if (connectivityManager != null && networkCallback != null) {
            connectivityManager.unregisterNetworkCallback(networkCallback);
        }
    }
}
