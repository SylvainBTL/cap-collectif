// @flow
/**
 * Copyright (c) 2013-present, Facebook, Inc.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 */

/**
 * https://github.com/zertosh/invariant/blob/master/invariant.js
 *
 * Use invariant() to assert state which your program assumes to be true.
 *
 * Provide sprintf-style format (only %s is supported) and arguments
 * to provide information about what broke and what you were
 * expecting.
 *
 * The invariant message will be stripped in production, but the invariant
 * will remain to ensure logic does not differ in production.
 */

const invariant = (
  condition: boolean,
  format: any,
  a: any,
  b: any,
  c: any,
  d: any,
  e: any,
  f: any,
) => {
  if (process.env.NODE_ENV === 'development') {
    if (format === undefined) {
      throw new Error('invariant requires an error message argument');
    }
  }

  if (!condition) {
    let error;
    if (format === undefined) {
      error = new Error(
        'Minified exception occurred; use the non-minified dev environment ' +
          'for the full error message and additional helpful warnings.',
      );
    } else {
      const args = [a, b, c, d, e, f];
      let argIndex = 0;
      error = new Error(`Invariant Violation: ${format.replace(/%s/g, () => args[argIndex++])}`);
    }

    // $FlowFixMe
    error.framesToPop = 1; // we don't care about invariant's own frame
    throw error;
  }
};

export default invariant;
