// @flow
import React, { lazy, Suspense } from 'react';
import Providers from './Providers';
import Loader from '~ui/FeedbacksIndicators/Loader';

const AccountBox = lazy(() =>
  import(/* webpackChunkName: "AccountBox" */ '~/components/User/Profile/AccountBox'),
);

export default (props: Object) => (
  <Suspense fallback={<Loader />}>
    <Providers>
      <AccountBox {...props} />
    </Providers>
  </Suspense>
);
