package com.mibvtu.webapp;

import android.app.Activity;
import android.content.Context;
import android.net.ConnectivityManager;
import android.net.Network;
import android.net.NetworkCapabilities;
import android.net.NetworkRequest;
import android.os.Bundle;
import android.webkit.CookieManager;
import android.webkit.WebSettings;
import android.webkit.WebView;
import android.webkit.WebViewClient;

public class MainActivity extends Activity {
    private WebView webView;
    private ConnectivityManager connectivityManager;
    private ConnectivityManager.NetworkCallback networkCallback;

    private final String LIVE_URL = "https://mibgroupltd.com";
    private final String OFFLINE_PAGE = "file:///android_asset/offline.html";
    private boolean isOfflinePageDisplayed = false;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);

        CookieManager.getInstance().setAcceptCookie(true);

        webView = new WebView(this);
        WebSettings settings = webView.getSettings();
        settings.setJavaScriptEnabled(true);
        settings.setDomStorageEnabled(true);
        settings.setCacheMode(WebSettings.LOAD_DEFAULT);
        settings.setAllowFileAccess(true);
        settings.setSaveFormData(true);
        settings.setUseWideViewPort(true);
        settings.setLoadWithOverviewMode(true);

        webView.setWebViewClient(new WebViewClient() {
            @Override
            public void onReceivedError(WebView view, int errorCode,
                                        String description, String failingUrl) {
                if (failingUrl != null && failingUrl.startsWith("https://mibgroupltd")) {
                    view.loadUrl(OFFLINE_PAGE);
                    isOfflinePageDisplayed = true;
                }
            }

            @Override
            public void onPageFinished(WebView view, String url) {
                if (url.equals(LIVE_URL)) {
                    isOfflinePageDisplayed = false;
                }
            }
        });

        setContentView(webView);

        // Network callback
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

        webView.loadUrl(LIVE_URL);
    }

    @Override
    protected void onDestroy() {
        super.onDestroy();
        if (connectivityManager != null && networkCallback != null) {
            connectivityManager.unregisterNetworkCallback(networkCallback);
        }
    }
}
