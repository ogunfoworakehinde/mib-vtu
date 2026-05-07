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
import android.webkit.CookieManager;
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

        // Accept cookies for sessions
        CookieManager.getInstance().setAcceptCookie(true);
        CookieManager.getInstance().setAcceptThirdPartyCookies(webView, true);

        // Create container
        RelativeLayout root = new RelativeLayout(this);

        // Splash layout
        splashView = getLayoutInflater().inflate(R.layout.activity_splash, root, false);
        root.addView(splashView);

        // WebView
        webView = new WebView(this);
        webView.setVisibility(View.INVISIBLE);
        WebSettings settings = webView.getSettings();
        settings.setJavaScriptEnabled(true);
        settings.setDomStorageEnabled(true);
        settings.setCacheMode(WebSettings.LOAD_DEFAULT);   // <-- use normal caching (respects headers)
        settings.setAllowFileAccess(true);
        settings.setSaveFormData(true);

        webView.setWebViewClient(new WebViewClient() {
            @Override
            public void onReceivedError(WebView view, int errorCode,
                                        String description, String failingUrl) {
                if (failingUrl != null && failingUrl.startsWith("https://mib-vtu")) {
                    webView.loadUrl(OFFLINE_PAGE);
                    isOfflinePageDisplayed = true;
                    splashView.setVisibility(View.GONE);
                    webView.setVisibility(View.VISIBLE);
                }
            }

            @Override
            public void onPageFinished(WebView view, String url) {
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

        // Start loading
        webView.loadUrl(LIVE_URL);

        // Fallback after 5 seconds
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
