import OpinionSource from './OpinionSource';

const OpinionSourceList = React.createClass({
  propTypes: {
    sources: React.PropTypes.array,
  },
  mixins: [ReactIntl.IntlMixin],

  render() {
    if (this.props.sources.length === 0) {
      return (
        <p className="text-center">
          <i className="cap-32 cap-baloon-1"></i>
          <br/>
          { this.getIntlMessage('opinion.no_new_version') }
        </p>
      );
    }

    const classes = React.addons.classSet({
      'media-list': true,
    });

    return (
      <ul className={classes} style={{ marginTop: '20px'}}>
        {
          this.props.sources.map((source) => {
            return <OpinionSource {...this.props} key={source.id} source={source} />;
          })
        }
      </ul>
    );
  },

});

export default OpinionSourceList;
