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
import android.util.Log;

import java.security.MessageDigest;
import java.security.cert.Certificate;
import javax.net.ssl.SSLPeerUnverifiedException;

public class MainActivity extends Activity {
    private WebView webView;
    private ConnectivityManager connectivityManager;
    private ConnectivityManager.NetworkCallback networkCallback;

    private final String LIVE_URL = "https://mibgroupltd.com";
    private final String OFFLINE_PAGE = "file:///android_asset/offline.html";
    private boolean isOfflinePageDisplayed = false;

    // Replace with your actual pin (output from the openssl command)
    private static final String PIN = "sha256/sI2sHnm0tofArkZ1wbY6avI+2F+38xc+LQf+uFM/YDA=";  // <-- YOUR REAL PIN HERE

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

            @Override
            public void onReceivedSslError(WebView view, android.webkit.SslErrorHandler handler, android.net.http.SslError error) {
                // Block all SSL errors (pinning fails)
                handler.cancel();
                view.loadUrl(OFFLINE_PAGE);
            }

            // Certificate pinning check
            @Override
            public void onPageCommitVisible(WebView view, String url) {
                if (url.startsWith("https://mibgroupltd")) {
                    try {
                        Certificate[] certs = view.getCertificate();
                        if (certs != null && certs.length > 0) {
                            byte[] pubKey = certs[0].getPublicKey().getEncoded();
                            MessageDigest md = MessageDigest.getInstance("SHA-256");
                            byte[] digest = md.digest(pubKey);
                            String hash = "sha256/" + android.util.Base64.encodeToString(digest, android.util.Base64.NO_WRAP);
                            if (!hash.equals(PIN)) {
                                throw new SSLPeerUnverifiedException("Certificate pin mismatch");
                            }
                        }
                    } catch (Exception e) {
                        // Pin check failed – block loading
                        webView.stopLoading();
                        webView.loadUrl(OFFLINE_PAGE);
                    }
                }
            }
        });

        setContentView(webView);

        // Network callback (unchanged)
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
