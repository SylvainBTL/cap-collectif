var CommentAuthor = React.createClass({

    render() {
        return (
            <p className="h5  opinion__user">
                { this.renderAuthorName() }
            </p>
        );
    },

    renderAuthorName() {
        if (this.props.comment.author) {
            return (
                <a href={this.props.comment.author._links.profile}>
                    { this.props.comment.author.username }
                </a>
            );
        }
        return <span>{ this.props.comment.authorName }</span>;
    }

});

export default CommentAuthor;
