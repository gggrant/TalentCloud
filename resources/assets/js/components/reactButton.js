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
        console.log(this.props);

        return (
            <button onClick={() => this.setState({ liked: true })}>
                { this.props.home_template.home_title }
            </button>
        );
    }
}

const domContainer = document.querySelector("#react_button");
if (domContainer) {
    // const props = Object.assign({}, domContainer.dataset);
    const home_template = JSON.parse(domContainer.getAttribute('data-home_template'));
    const props = { home_template: home_template};

    console.log(props);
    ReactDOM.render(<ReactButton { ...props } />, domContainer);
}
