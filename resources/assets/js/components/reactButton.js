var React = require('react');
var ReactDOM = require('react-dom');

class ReactButton extends React.Component {
    constructor(props) {
        super(props);
        this.state = { liked: false };
    }
    render() {
        if (this.state.liked) {
            return 'You liked this.';
        }

        return (
            <button onClick={() => this.setState({ liked: true })}>
                Like
            </button>
        );
    }
}

const domContainer = document.querySelector("#react_button");
ReactDOM.render(<ReactButton />, domContainer);
