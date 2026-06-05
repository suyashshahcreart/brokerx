import $ from "jquery";
import select2Factory from "select2";

window.$ = window.jQuery = $;

const attachSelect2 =
	typeof select2Factory === "function"
		? select2Factory
		: select2Factory?.default;

if (typeof $.fn.select2 !== "function" && typeof attachSelect2 === "function") {
	attachSelect2(window, $);
}

export default $;
