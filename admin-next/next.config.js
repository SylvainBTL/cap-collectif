// @ts-check

/**
 * @type {import('next/dist/next-server/server/config').NextConfig}
 **/
const nextConfig = {
  // TODO enable this
  webpack5: false,
  // TODO enable this
  reactStrictMode: false,
  basePath:
    process.env.SYMFONY_ENV === 'prod' || process.env.SYMFONY_ENV === 'test' ? '/admin-next' : '',
  env: {
    PRODUCTION: process.env.SYMFONY_ENV === 'prod',
  },
  i18n: {
    localeDetection: true,
    defaultLocale: 'fr-FR',
    locales: ['fr-FR', 'es-ES', 'en-GB', 'de-DE', 'nl-NL', 'sv-SE', 'eu-EU', 'oc-OC'],
  },
  excludeDefaultMomentLocales: true,
  experimental: {
    externalDir: true,
  },
  typescript: {
    // !! WARN !!
    // Dangerously allow production builds to successfully complete even if
    // your project has type errors.
    // !! WARN !!
    ignoreBuildErrors: true,
  },
  webpack(config, { isServer }) {
    config.module.rules.push({
      test: /\.svg$/,
      issuer: {
        test: /\.(js|ts)x?$/,
      },
      use: ['@svgr/webpack'],
    });
    config.module.rules.push({
      test: /\.(js|jsx)$/,
      include: /..\/node_modules\/(?=(react-leaflet|@react-leaflet\/core)\/).*/,
      loader: require.resolve('babel-loader'),
      options: {
        presets: ['@babel/preset-env', '@babel/preset-react'],
        plugins: ['@babel/plugin-proposal-nullish-coalescing-operator'],
      },
    });

    if (!isServer) {
      config.node = {
        net: 'empty',
        tls: 'empty',
        child_process: 'empty',
      };
    }

    return config;
  },
};

module.exports = nextConfig;
