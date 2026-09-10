import { renderLabelWithAsterisk } from './Input';

export default function Select({ label, id, required, error, options = [], className = "", ...props }) {
  return (
    <div className={`mb-5 ${className}`}>
      {label && (
        <label htmlFor={id} className="block mb-2 font-medium text-textMain">
          {renderLabelWithAsterisk(label, required)}
        </label>
      )}
      <select
        id={id}
        className={`w-full px-4 py-3 border rounded-md font-sarabun text-[15px] transition-colors focus:outline-none focus:border-primary focus:ring-4 focus:ring-primary/10 appearance-none bg-white ${
          error ? "border-error bg-red-50" : "border-border"
        }`}
        {...props}
      >
        <option value="" disabled>-- กรุณาเลือก --</option>
        {options.map(opt => (
          <option key={opt.value} value={opt.value}>{opt.label}</option>
        ))}
      </select>
      {/* Custom arrow for select */}
      <div className="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-500 top-[35px]">
        <i className="fas fa-chevron-down text-sm"></i>
      </div>
      {error && <p className="mt-1 text-sm text-error">{error}</p>}
    </div>
  );
}
